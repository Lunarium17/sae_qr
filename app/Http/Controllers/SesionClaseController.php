<?php

namespace App\Http\Controllers;

use App\Models\SesionClase;
use App\Models\Materia;
use App\Models\Matricula;
use App\Models\RegistroAsistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SesionClaseController extends Controller
{
    // Mostrar panel de sesiones de clase y escáner en vivo
    public function index(Request $request)
    {
        $materias = Materia::with('grupo')->orderBy('grupo_id')->get();
        $materiaSeleccionada = $request->query('materia_id');
        
        // Buscar si el profesor tiene una sesión activa en este momento
        $sesionActiva = SesionClase::with(['materia.grupo', 'asistencias.alumno'])
            ->where('profesor_id', Auth::id())
            ->where('estado', 'activa')
            ->first();

        // Historial de sesiones anteriores
        $historialSesiones = SesionClase::with(['materia.grupo', 'asistencias'])
            ->where('profesor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profesor.sesiones', compact('materias', 'sesionActiva', 'historialSesiones', 'materiaSeleccionada'));
    }

    // Abrir una nueva sesión de clase
    public function iniciarSesion(Request $request)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'tolerancia_minutos' => 'required|integer|min:0|max:60',
        ]);

        // Cerrar cualquier sesión que haya quedado abierta por error
        SesionClase::where('profesor_id', Auth::id())
            ->where('estado', 'activa')
            ->update(['estado' => 'finalizada', 'hora_fin' => Carbon::now()->toTimeString()]);

        // Crear la nueva sesión activa
        SesionClase::create([
            'profesor_id' => Auth::id(),
            'materia_id' => $request->materia_id,
            'fecha' => Carbon::now()->toDateString(),
            'hora_inicio' => Carbon::now()->toTimeString(),
            'hora_fin' => Carbon::now()->addHours(2)->toTimeString(),
            'tolerancia_minutos' => $request->tolerancia_minutos,
            'estado' => 'activa',
        ]);

        return redirect()->route('sesiones.index')
                         ->with('success', 'Sesión de clase iniciada. Ya puedes escanear los códigos QR de los alumnos.');
    }

    // Cerrar la sesión activa y asignar falta a los alumnos no escaneados
    public function finalizarSesion(SesionClase $sesion)
    {
        $sesion->update([
            'estado' => 'finalizada',
            'hora_fin' => Carbon::now()->toTimeString(),
        ]);

        // Buscar todos los alumnos con matrícula 'aceptado' en esta materia
        $alumnosInscritos = Matricula::where('materia_id', $sesion->materia_id)
            ->where('estado', 'aceptado')
            ->pluck('user_id');

        // A los que no tengan asistencia registrada en esta sesión, se les registra 'falta'
        foreach ($alumnosInscritos as $alumnoId) {
            $tieneRegistro = RegistroAsistencia::where('sesion_clase_id', $sesion->id)
                ->where('user_id', $alumnoId)
                ->exists();

            if (!$tieneRegistro) {
                RegistroAsistencia::create([
                    'sesion_clase_id' => $sesion->id,
                    'user_id' => $alumnoId,
                    'timestamp_escaneo' => null,
                    'estado_incidencia' => 'falta',
                    'observaciones' => 'Falta automática al cierre de sesión (Sin escaneo QR)',
                ]);
            }
        }

        return redirect()->route('sesiones.index')
                         ->with('success', 'Sesión finalizada. Se asignó falta automática a los alumnos no escaneados.');
    }

    // Permitir al profesor modificar manualmente el estado de asistencia (Ej. Permiso)
    public function cambiarEstadoManual(Request $request, RegistroAsistencia $registro)
    {
        $request->validate([
            'estado_incidencia' => 'required|in:asistencia,retardo,falta,permiso',
        ]);

        $registro->update([
            'estado_incidencia' => $request->estado_incidencia,
            'observaciones' => 'Ajustado manualmente por el profesor',
        ]);

        return back()->with('success', 'Estado de asistencia actualizado manualmente.');
    }
}