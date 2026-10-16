<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Grupo;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Importamos la fachada Auth para eliminar el error en VS Code

class MatriculaController extends Controller
{
    // ==========================================
    // FUNCIONES PARA EL ALUMNO
    // ==========================================

    // Mostrar vista para buscar grupo/materia y ver estado de solicitudes
    public function crearSolicitud()
    {
        $grupos = Grupo::all();
        $materias = Materia::all();
        $misMatriculas = Matricula::with(['grupo', 'materia'])
            ->where('user_id', Auth::id()) // <-- Corregido con Auth::id()
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

        // Verificar que no haya solicitado ya la misma materia en el mismo grupo
        $existe = Matricula::where('user_id', Auth::id()) // <-- Corregido con Auth::id()
            ->where('grupo_id', $request->grupo_id)
            ->where('materia_id', $request->materia_id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya enviaste una solicitud para este grupo y materia.');
        }

        Matricula::create([
            'user_id' => Auth::id(), // <-- Corregido con Auth::id()
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

    // Aprobar o rechazar solicitud (RF-03)
    public function actualizarEstado(Request $request, Matricula $matricula)
    {
        $request->validate([
            'estado' => 'required|in:aceptado,rechazado',
        ]);

        $matricula->update([
            'estado' => $request->estado,
        ]);

        $mensaje = $request->estado === 'aceptado' 
            ? 'Alumno aceptado en el grupo exitosamente.' 
            : 'Solicitud rechazada.';

        return back()->with('success', $mensaje);
    }
}