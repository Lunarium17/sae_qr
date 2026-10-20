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
    // FUNCIONES PARA EL ALUMNO
    // ==========================================

    // Mostrar vista para buscar grupo/materia y ver estado de solicitudes
    public function crearSolicitud()
    {
        $grupos = Grupo::with('materias')->get();
        $materias = Materia::with('grupo')->get();
        $misMatriculas = Matricula::with(['grupo', 'materia'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('alumno.solicitar_grupo', compact('grupos', 'materias', 'misMatriculas'));
    }

    // Guardar la solicitud del alumno en estado 'pendiente'
    public function guardarSolicitud(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
        ]);

        // Verificar que la materia pertenezca al grupo seleccionado (si la materia tiene grupo asignado)
        $materia = Materia::findOrFail($request->materia_id);
        if (!is_null($materia->grupo_id) && (int) $materia->grupo_id !== (int) $request->grupo_id) {
            return back()->with('error', 'La materia seleccionada no pertenece al grupo elegido.');
        }

        // Verificar que no haya solicitado ya la misma materia en el mismo grupo
        $existe = Matricula::where('user_id', Auth::id())
            ->where('grupo_id', $request->grupo_id)
            ->where('materia_id', $request->materia_id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya enviaste una solicitud para este grupo y materia.');
        }

        Matricula::create([
            'user_id' => Auth::id(),
            'grupo_id' => $request->grupo_id,
            'materia_id' => $request->materia_id,
            'estado' => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud de inscripción enviada. Espera la aprobación de tu profesor.');
    }

    // ==========================================
    // FUNCIONES PARA EL PROFESOR
    // ==========================================

    // Listar todas las solicitudes de alumnos
    public function indexSolicitudes()
    {
        $solicitudes = Matricula::with(['alumno', 'grupo', 'materia'])
            ->orderByRaw("FIELD(estado, 'pendiente', 'aceptado', 'rechazado')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profesor.solicitudes', compact('solicitudes'));
    }

    // Inscribir manualmente a un alumno en una materia (directamente como 'aceptado')
    public function inscribirManual(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'grupo_id'   => 'nullable|exists:grupos,id',
        ], [
            'user_id.required'    => 'Debes seleccionar un alumno.',
            'materia_id.required' => 'Debes seleccionar una materia.',
        ]);

        $alumno = User::where('id', $request->user_id)->where('rol', 'alumno')->firstOrFail();
        $materia = Materia::findOrFail($request->materia_id);
        $grupoId = $materia->grupo_id ?? $request->grupo_id;

        if (!$grupoId) {
            return back()->with('error', 'La materia debe estar asociada a un grupo para inscribir alumnos.');
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

    // Aprobar o rechazar solicitud / cambiar estado de inscripción (RF-03)
    public function actualizarEstado(Request $request, Matricula $matricula)
    {
        $request->validate([
            'estado' => 'required|in:aceptado,rechazado,pendiente',
        ]);

        $matricula->update([
            'estado' => $request->estado,
        ]);

        $mensaje = match ($request->estado) {
            'aceptado'  => 'Estado del alumno actualizado a Aceptado.',
            'rechazado' => 'Estado del alumno actualizado a Rechazado.',
            default     => 'Estado de la inscripción actualizado.',
        };

        return back()->with('success', $mensaje);
    }

    // Dar de baja (eliminar inscripción) a un alumno de una materia
    public function destroy(Matricula $matricula)
    {
        $nombreAlumno = $matricula->alumno->name ?? 'El alumno';
        $matricula->delete();

        return back()->with('success', $nombreAlumno . ' fue dado de baja de la materia correctamente.');
    }
}