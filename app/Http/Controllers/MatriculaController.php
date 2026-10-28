<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatriculaController extends Controller
{
    // ==========================================
    // FUNCIONES PARA EL ALUMNO (Matrículas Flexibles e Independientes por Asignatura)
    // ==========================================

    // Mostrar vista para seleccionar grupo y buscar asignaturas dinámicamente (sin cargar todas las materias de golpe)
    public function crearSolicitud()
    {
        $grupos = Grupo::orderBy('nombre_grupo')->get();
        $misMatriculas = Matricula::with(['grupo', 'materia.profesor'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('alumno.solicitar_grupo', compact('grupos', 'misMatriculas'));
    }

    // Endpoint AJAX para obtener dinámicamente las asignaturas de un grupo (con filtro opcional por texto)
    public function materiasPorGrupo(Request $request, Grupo $grupo)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Materia::with('profesor')
            ->where('grupo_id', $grupo->id);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre_materia', 'like', "%{$q}%")
                    ->orWhere('codigo_materia', 'like', "%{$q}%");
            });
        }

        $materias = $query->orderBy('nombre_materia')->limit(50)->get();

        $matriculasAlumno = Matricula::where('user_id', Auth::id())
            ->where('grupo_id', $grupo->id)
            ->pluck('estado', 'materia_id');

        $resultado = $materias->map(function ($m) use ($matriculasAlumno) {
            return [
                'id'               => $m->id,
                'codigo_materia'   => $m->codigo_materia,
                'nombre_materia'   => $m->nombre_materia,
                'profesor_nombre'  => optional($m->profesor)->name ?? 'Docente por asignar',
                'estado_matricula' => $matriculasAlumno->get($m->id), // null, 'pendiente', 'aceptado', 'rechazado'
            ];
        });

        return response()->json($resultado);
    }

    // Guardar solicitud individual para la combinación exacta (MateriaID + GrupoID)
    public function guardarSolicitud(Request $request)
    {
        $request->validate([
            'grupo_id'   => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
        ]);

        // Verificar que la asignatura pertenezca al grupo seleccionado
        $materia = Materia::findOrFail($request->materia_id);
        if (!is_null($materia->grupo_id) && (int) $materia->grupo_id !== (int) $request->grupo_id) {
            return back()->with('error', 'La asignatura seleccionada no corresponde al grupo elegido.');
        }

        // Verificar que el alumno no haya solicitado ya la misma combinación (GrupoID + MateriaID)
        $existe = Matricula::where('user_id', Auth::id())
            ->where('grupo_id', $request->grupo_id)
            ->where('materia_id', $request->materia_id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya enviaste una solicitud para esta asignatura en el grupo seleccionado.');
        }

        Matricula::create([
            'user_id'    => Auth::id(),
            'grupo_id'   => $request->grupo_id,
            'materia_id' => $request->materia_id,
            'estado'     => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud de inscripción por asignatura enviada. El docente titular de esa materia revisará tu solicitud.');
    }

    // ==========================================
    // FUNCIONES PARA EL PROFESOR (Revisión y Búsqueda Dinámica por Asignatura y Grupo)
    // ==========================================

    // Endpoint AJAX: Buscar alumnos por nombre, matrícula o correo (máximo 15 resultados)
    public function buscarAlumnos(Request $request)
    {
        $termino   = trim((string) $request->query('q', ''));
        $materiaId = (int) $request->query('materia_id', 0);

        if (mb_strlen($termino) < 2) {
            return response()->json([]);
        }

        $alumnos = User::where('rol', 'alumno')
            ->where(function ($q) use ($termino) {
                $q->where('name', 'like', "%{$termino}%")
                  ->orWhere('matricula_codigo', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email', 'matricula_codigo']);

        $idsAceptados = [];
        if ($materiaId > 0) {
            $idsAceptados = Matricula::where('materia_id', $materiaId)
                ->where('estado', 'aceptado')
                ->whereIn('user_id', $alumnos->pluck('id'))
                ->pluck('user_id')
                ->toArray();
        }

        $resultado = $alumnos->map(function ($al) use ($idsAceptados) {
            return [
                'id'               => $al->id,
                'name'             => $al->name,
                'email'            => $al->email,
                'matricula_codigo' => $al->matricula_codigo ?? 'Sin matrícula',
                'ya_inscrito'      => in_array($al->id, $idsAceptados),
            ];
        });

        return response()->json($resultado);
    }

    // Listar solicitudes e inscripciones de las asignaturas del profesor con buscador, filtros y paginación
    public function indexSolicitudes(Request $request)
    {
        $profesorId    = Auth::id();
        $buscar        = trim((string) $request->query('buscar', ''));
        $estadoFiltro  = $request->query('estado', 'todos');
        $materiaFiltro = (int) $request->query('materia_id', 0);

        $misMaterias = Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->orderBy('grupo_id')
            ->get();

        $query = Matricula::with(['alumno', 'grupo', 'materia.profesor'])
            ->whereHas('materia', function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            });

        if (in_array($estadoFiltro, ['pendiente', 'aceptado', 'rechazado'], true)) {
            $query->where('estado', $estadoFiltro);
        }

        if ($materiaFiltro > 0) {
            $query->where('materia_id', $materiaFiltro);
        }

        if ($buscar !== '') {
            $query->whereHas('alumno', function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('matricula_codigo', 'like', "%{$buscar}%");
            });
        }

        $solicitudes = $query
            ->orderByRaw("FIELD(estado, 'pendiente', 'aceptado', 'rechazado')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('profesor.solicitudes', compact(
            'solicitudes',
            'misMaterias',
            'buscar',
            'estadoFiltro',
            'materiaFiltro'
        ));
    }

    // Inscribir manualmente a un alumno en una combinación exacta (GrupoID + MateriaID) que imparte el profesor
    public function inscribirManual(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'grupo_id'   => 'nullable|exists:grupos,id',
        ], [
            'user_id.required'    => 'Debes buscar y seleccionar un alumno de la lista.',
            'materia_id.required' => 'Debes seleccionar una asignatura.',
        ]);

        $profesorId = Auth::id();
        $alumno  = User::where('id', $request->user_id)->where('rol', 'alumno')->firstOrFail();
        $materia = Materia::findOrFail($request->materia_id);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) $profesorId) {
            return back()->with('error', 'Solo puedes inscribir alumnos en las asignaturas que te corresponden impartir.');
        }

        $grupoId = $materia->grupo_id ?? $request->grupo_id;

        if (!$grupoId) {
            return back()->with('error', 'La asignatura debe estar asociada a un grupo para inscribir alumnos.');
        }

        $matricula = Matricula::updateOrCreate(
            [
                'user_id'    => $alumno->id,
                'grupo_id'   => $grupoId,
                'materia_id' => $materia->id,
            ],
            [
                'estado' => 'aceptado',
            ]
        );

        $accion = $matricula->wasRecentlyCreated ? 'inscrito' : 'actualizado como aceptado';

        return back()->with('success', 'El alumno ' . $alumno->name . ' ha sido ' . $accion . ' en ' . $materia->nombre_materia . '.');
    }

    // Aprobar o rechazar solicitud únicamente de asignaturas que imparte este profesor
    public function actualizarEstado(Request $request, Matricula $matricula)
    {
        $request->validate([
            'estado' => 'required|in:aceptado,rechazado,pendiente',
        ]);

        $matricula->loadMissing('materia');
        if ($matricula->materia && !is_null($matricula->materia->profesor_id) && (int) $matricula->materia->profesor_id !== (int) Auth::id()) {
            return back()->with('error', 'No tienes permiso para gestionar solicitudes de una asignatura impartida por otro docente.');
        }

        $matricula->update([
            'estado' => $request->estado,
        ]);

        $mensaje = match ($request->estado) {
            'aceptado'  => 'Solicitud aprobada: el alumno ha quedado inscrito en tu asignatura y grupo.',
            'rechazado' => 'Solicitud rechazada para esta asignatura.',
            default     => 'Estado de la inscripción actualizado.',
        };

        return back()->with('success', $mensaje);
    }

    // Dar de baja (eliminar inscripción) a un alumno de una asignatura que imparte este profesor
    public function destroy(Matricula $matricula)
    {
        $matricula->loadMissing(['alumno', 'materia']);
        if ($matricula->materia && !is_null($matricula->materia->profesor_id) && (int) $matricula->materia->profesor_id !== (int) Auth::id()) {
            return back()->with('error', 'No tienes permiso para dar de baja alumnos de una asignatura impartida por otro docente.');
        }

        $nombreAlumno = $matricula->alumno->name ?? 'El alumno';
        $matricula->delete();

        return back()->with('success', $nombreAlumno . ' fue dado de baja de la asignatura correctamente.');
    }
}
