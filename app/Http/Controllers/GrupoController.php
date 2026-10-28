<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\RegistroAsistencia;
use App\Models\SesionClase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GrupoController extends Controller
{
    /**
     * Mostrar la lista de grupos académicos universitarios con estadísticas modulares del docente.
     */
    public function index()
    {
        $profesorId = Auth::id();

        $grupos = Grupo::with(['materias.profesor'])
            ->withCount([
                'materias',
                'materias as mis_materias_count' => function ($q) use ($profesorId) {
                    $q->where('profesor_id', $profesorId)
                      ->orWhereNull('profesor_id');
                },
                'matriculas as alumnos_aceptados_count' => function ($q) use ($profesorId) {
                    $q->where('estado', 'aceptado')
                      ->whereHas('materia', function ($sub) use ($profesorId) {
                          $sub->where('profesor_id', $profesorId)
                              ->orWhereNull('profesor_id');
                      });
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular porcentaje de asistencia para las asignaturas que imparte el docente en cada grupo
        foreach ($grupos as $grupo) {
            $misMateriaIds = $grupo->materias
                ->filter(fn ($m) => is_null($m->profesor_id) || (int) $m->profesor_id === (int) $profesorId)
                ->pluck('id');

            if ($misMateriaIds->isEmpty()) {
                $grupo->porcentaje_asistencia = null;
                $grupo->total_registros_grupo = 0;
                continue;
            }

            $registros = RegistroAsistencia::whereHas('sesion', function ($q) use ($misMateriaIds, $grupo) {
                $q->whereIn('materia_id', $misMateriaIds)
                  ->where(function ($sub) use ($grupo) {
                      $sub->where('grupo_id', $grupo->id)
                          ->orWhereNull('grupo_id');
                  });
            })->get();

            $total = $registros->count();
            $efectivas = $registros->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();

            $grupo->total_registros_grupo = $total;
            $grupo->porcentaje_asistencia = $total > 0 ? round(($efectivas / $total) * 100) : null;
        }

        return view('grupos.index', compact('grupos'));
    }

    /**
     * Mostrar el formulario para crear un nuevo grupo universitario.
     */
    public function create()
    {
        return view('grupos.create');
    }

    /**
     * Guardar el nuevo grupo académico en la BD.
     */
    public function store(Request $request)
    {
        if ($request->filled('nombre') && !$request->filled('nombre_grupo')) {
            $request->merge(['nombre_grupo' => $request->input('nombre')]);
        }

        $request->validate([
            'nombre_grupo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grupos', 'nombre_grupo')->where(function ($query) use ($request) {
                    return $query->where('turno', $request->turno);
                }),
            ],
            'carrera'         => 'required|string|max:120',
            'grado'           => 'required|string|max:50',
            'turno'           => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ], [
            'nombre_grupo.required'    => 'El nombre del grupo es obligatorio.',
            'nombre_grupo.unique'      => 'Ya existe un grupo llamado "' . $request->nombre_grupo . '" en el turno ' . $request->turno . '. Solo puede registrarse en un turno diferente.',
            'carrera.required'         => 'La carrera o programa educativo es obligatorio.',
            'grado.required'           => 'El semestre es obligatorio.',
            'turno.required'           => 'Debes seleccionar un turno.',
            'periodo_lectivo.required' => 'El periodo lectivo es obligatorio.',
        ]);

        Grupo::create([
            'nombre_grupo'    => $request->nombre_grupo,
            'carrera'         => $request->carrera,
            'grado'           => $request->grado,
            'turno'           => $request->turno,
            'periodo_lectivo' => $request->periodo_lectivo,
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo académico creado exitosamente.');
    }

    /**
     * Mostrar el panel del grupo con sus asignaturas (modulares por docente), matrículas y estadísticas.
     */
    public function show(string $id)
    {
        $profesorId = Auth::id();

        $grupo = Grupo::with([
            'materias.profesor',
            'materias.matriculas' => function ($q) {
                $q->where('estado', 'aceptado')->with('alumno');
            },
            'matriculas' => function ($q) use ($profesorId) {
                $q->with(['alumno', 'materia.profesor'])
                  ->whereHas('materia', function ($sub) use ($profesorId) {
                      $sub->where('profesor_id', $profesorId)
                          ->orWhereNull('profesor_id');
                  })
                  ->orderByRaw("FIELD(estado, 'pendiente', 'aceptado', 'rechazado')")
                  ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Asignaturas que este docente imparte dentro de este grupo
        $misMateriasGrupo = $grupo->materias->filter(
            fn ($m) => is_null($m->profesor_id) || (int) $m->profesor_id === (int) $profesorId
        );
        $materiaIds = $misMateriasGrupo->pluck('id');

        $totalSesionesGrupo = $materiaIds->isNotEmpty()
            ? SesionClase::whereIn('materia_id', $materiaIds)
                ->where(function ($q) use ($grupo) {
                    $q->where('grupo_id', $grupo->id)->orWhereNull('grupo_id');
                })
                ->count()
            : 0;

        $registrosGrupo = $materiaIds->isNotEmpty()
            ? RegistroAsistencia::with('sesion')->whereHas('sesion', function ($q) use ($materiaIds, $grupo) {
                $q->whereIn('materia_id', $materiaIds)
                  ->where(function ($sub) use ($grupo) {
                      $sub->where('grupo_id', $grupo->id)->orWhereNull('grupo_id');
                  });
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

        // Estadísticas por cada materia del grupo y conteo de faltas por alumno-materia (aplicando regla universitaria: 3 retardos = 1 falta)
        $faltasPorAlumnoMateria = [];
        $faltasDirectasPorAlumnoMateria = [];
        $retardosPorAlumnoMateria = [];
        foreach ($grupo->materias as $materia) {
            $regsMat = RegistroAsistencia::with('sesion')->whereHas('sesion', function ($q) use ($materia, $grupo) {
                $q->where('materia_id', $materia->id)
                  ->where(function ($sub) use ($grupo) {
                      $sub->where('grupo_id', $grupo->id)->orWhereNull('grupo_id');
                  });
            })->get();

            $totMat = $regsMat->count();
            $efecMat = $regsMat->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
            $materia->porcentaje_asistencia = $totMat > 0 ? round(($efecMat / $totMat) * 100) : null;
            $materia->total_registros = $totMat;

            foreach ($regsMat->groupBy('user_id') as $uid => $regsAlumno) {
                $clave = $materia->id . '_' . $uid;
                $faltasDirectas = $regsAlumno->where('estado_incidencia', 'falta')->count();
                $retardosAcum   = $regsAlumno->where('estado_incidencia', 'retardo')->count();
                $faltasPorRet   = intdiv($retardosAcum, 3);

                $faltasDirectasPorAlumnoMateria[$clave] = $faltasDirectas;
                $retardosPorAlumnoMateria[$clave]       = $retardosAcum;
                $faltasPorAlumnoMateria[$clave]         = $faltasDirectas + $faltasPorRet;
            }
        }

        return view('grupos.show', compact(
            'grupo',
            'misMateriasGrupo',
            'statsGrupo',
            'faltasPorAlumnoMateria',
            'faltasDirectasPorAlumnoMateria',
            'retardosPorAlumnoMateria'
        ));
    }

    /**
     * Mostrar el formulario para editar un grupo existente.
     */
    public function edit(string $id)
    {
        $grupo = Grupo::findOrFail($id);
        return view('grupos.edit', compact('grupo'));
    }

    /**
     * Actualizar los datos del grupo en la BD.
     */
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
            'carrera'         => 'required|string|max:120',
            'grado'           => 'required|string|max:50',
            'turno'           => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ], [
            'nombre_grupo.required'    => 'El nombre del grupo es obligatorio.',
            'nombre_grupo.unique'      => 'Ya existe otro grupo llamado "' . $request->nombre_grupo . '" en el turno ' . $request->turno . '.',
            'carrera.required'         => 'La carrera o programa educativo es obligatorio.',
            'grado.required'           => 'El semestre es obligatorio.',
            'turno.required'           => 'Debes seleccionar un turno.',
            'periodo_lectivo.required' => 'El periodo lectivo es obligatorio.',
        ]);

        $grupo->update([
            'nombre_grupo'    => $request->nombre_grupo,
            'carrera'         => $request->carrera,
            'grado'           => $request->grado,
            'turno'           => $request->turno,
            'periodo_lectivo' => $request->periodo_lectivo,
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo actualizado correctamente.');
    }

    /**
     * Eliminar un grupo de la BD (solo si no tiene asignaturas de otros profesores).
     */
    public function destroy(Grupo $grupo)
    {
        $tieneMateriasDeOtrosDocentes = $grupo->materias()
            ->whereNotNull('profesor_id')
            ->where('profesor_id', '!=', Auth::id())
            ->exists();

        if ($tieneMateriasDeOtrosDocentes) {
            return redirect()->route('grupos.index')
                ->with('error', 'No se puede eliminar este grupo porque otros docentes imparten asignaturas en él. Elimina únicamente tus asignaturas desde el detalle del grupo.');
        }

        $grupo->delete();
        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado correctamente.');
    }
}
