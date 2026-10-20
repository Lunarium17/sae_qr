<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\RegistroAsistencia;
use App\Models\SesionClase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // mostrar la lista de grupos con estadísticas de asistencia
    public function index()
    {
        $grupos = Grupo::with(['materias'])
            ->withCount([
                'materias',
                'matriculas as alumnos_aceptados_count' => function ($q) {
                    $q->where('estado', 'aceptado');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular porcentaje de asistencia para cada grupo
        foreach ($grupos as $grupo) {
            $materiaIds = $grupo->materias->pluck('id');
            if ($materiaIds->isEmpty()) {
                $grupo->porcentaje_asistencia = null;
                $grupo->total_registros_grupo = 0;
                continue;
            }

            $registros = RegistroAsistencia::whereHas('sesion', function ($q) use ($materiaIds) {
                $q->whereIn('materia_id', $materiaIds);
            })->get();

            $total = $registros->count();
            $efectivas = $registros->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();

            $grupo->total_registros_grupo = $total;
            $grupo->porcentaje_asistencia = $total > 0 ? round(($efectivas / $total) * 100) : null;
        }

        return view('grupos.index', compact('grupos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // mostrar el formulario para crear un nuevo grupo
    public function create()
    {
        return view('grupos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    // guardar el nuevo grupo en la BD
    public function store(Request $request)
    {
        // Compatibilidad si el formulario envía 'nombre' o 'nombre_grupo'
        if ($request->filled('nombre') && !$request->filled('nombre_grupo')) {
            $request->merge(['nombre_grupo' => $request->input('nombre')]);
        }

        // 1. Validar los datos recibidos del formulario (no permitir duplicados en el mismo turno)
        $request->validate([
            'nombre_grupo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grupos', 'nombre_grupo')->where(function ($query) use ($request) {
                    return $query->where('turno', $request->turno);
                }),
            ],
            'grado'           => 'required|string|max:50',
            'turno'           => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ], [
            'nombre_grupo.required'    => 'El nombre del grupo es obligatorio.',
            'nombre_grupo.unique'      => 'Ya existe un grupo llamado "' . $request->nombre_grupo . '" en el turno ' . $request->turno . '. Solo puede registrarse en un turno diferente (ej. Matutino o Vespertino).',
            'grado.required'           => 'El grado o semestre es obligatorio.',
            'turno.required'           => 'Debes seleccionar un turno.',
            'periodo_lectivo.required' => 'El periodo lectivo es obligatorio.',
        ]);

        // 2. Crear el registro en la base de datos (Modelo Grupo)
        Grupo::create([
            'nombre_grupo'    => $request->nombre_grupo,
            'grado'           => $request->grado,
            'turno'           => $request->turno,
            'periodo_lectivo' => $request->periodo_lectivo,
        ]);

        // 3. Redirigir de vuelta al índice de grupos con un mensaje de éxito
        return redirect()->route('grupos.index')->with('success', 'Grupo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    // Mostrar el panel unificado del grupo con sus materias, alumnos inscritos y estadísticas
    public function show(string $id)
    {
        $grupo = Grupo::with([
            'materias.matriculas' => function ($q) {
                $q->where('estado', 'aceptado')->with('alumno');
            },
            'matriculas' => function ($q) {
                $q->with(['alumno', 'materia'])
                  ->orderByRaw("FIELD(estado, 'pendiente', 'aceptado', 'rechazado')")
                  ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        $alumnosDisponibles = \App\Models\User::where('rol', 'alumno')->orderBy('name')->get();

        // Estadísticas de asistencia del grupo y desglose por materia
        $materiaIds = $grupo->materias->pluck('id');
        $totalSesionesGrupo = $materiaIds->isNotEmpty()
            ? SesionClase::whereIn('materia_id', $materiaIds)->count()
            : 0;

        $registrosGrupo = $materiaIds->isNotEmpty()
            ? RegistroAsistencia::with('sesion')->whereHas('sesion', function ($q) use ($materiaIds) {
                $q->whereIn('materia_id', $materiaIds);
            })->get()
            : collect();

        $statsGrupo = [
            'total_sesiones'  => $totalSesionesGrupo,
            'total_registros' => $registrosGrupo->count(),
            'asistencias'     => $registrosGrupo->where('estado_incidencia', 'asistencia')->count(),
            'retardos'        => $registrosGrupo->where('estado_incidencia', 'retardo')->count(),
            'permisos'        => $registrosGrupo->where('estado_incidencia', 'permiso')->count(),
            'faltas'          => $registrosGrupo->where('estado_incidencia', 'falta')->count(),
        ];
        $efectivasGrupo = $statsGrupo['asistencias'] + $statsGrupo['retardos'] + $statsGrupo['permisos'];
        $statsGrupo['porcentaje'] = $statsGrupo['total_registros'] > 0
            ? round(($efectivasGrupo / $statsGrupo['total_registros']) * 100)
            : 0;

        // Estadísticas por cada materia del grupo y conteo de faltas por alumno-materia
        $faltasPorAlumnoMateria = [];
        foreach ($grupo->materias as $materia) {
            $regsMat = $registrosGrupo->filter(fn ($r) => $r->sesion && (int) $r->sesion->materia_id === (int) $materia->id);
            $totMat = $regsMat->count();
            $efecMat = $regsMat->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
            $materia->porcentaje_asistencia = $totMat > 0 ? round(($efecMat / $totMat) * 100) : null;
            $materia->total_registros = $totMat;

            foreach ($regsMat->where('estado_incidencia', 'falta')->groupBy('user_id') as $uid => $faltasAlumno) {
                $faltasPorAlumnoMateria[$materia->id . '_' . $uid] = $faltasAlumno->count();
            }
        }

        return view('grupos.show', compact('grupo', 'alumnosDisponibles', 'statsGrupo', 'faltasPorAlumnoMateria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Mostrar el formulario para editar un grupo existente
    public function edit(string $id)
    {
        $grupo = Grupo::findOrFail($id);
        return view('grupos.edit', compact('grupo'));
    }

    /**
     * Update the specified resource in storage.
     */
    // Actualizar los datos del grupo en la BD
    public function update(Request $request, string $id)
    {
        $grupo = Grupo::findOrFail($id);

        if ($request->filled('nombre') && !$request->filled('nombre_grupo')) {
            $request->merge(['nombre_grupo' => $request->input('nombre')]);
        }

        $request->validate([
            'nombre_grupo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grupos', 'nombre_grupo')
                    ->where(function ($query) use ($request) {
                        return $query->where('turno', $request->turno);
                    })
                    ->ignore($grupo->id),
            ],
            'grado'           => 'required|string|max:50',
            'turno'           => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ], [
            'nombre_grupo.required'    => 'El nombre del grupo es obligatorio.',
            'nombre_grupo.unique'      => 'Ya existe otro grupo llamado "' . $request->nombre_grupo . '" en el turno ' . $request->turno . '.',
            'grado.required'           => 'El grado o semestre es obligatorio.',
            'turno.required'           => 'Debes seleccionar un turno.',
            'periodo_lectivo.required' => 'El periodo lectivo es obligatorio.',
        ]);

        $grupo->update([
            'nombre_grupo'    => $request->nombre_grupo,
            'grado'           => $request->grado,
            'turno'           => $request->turno,
            'periodo_lectivo' => $request->periodo_lectivo,
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    // eliminar un grupo de la BD
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado correctamente.');
    }
}
