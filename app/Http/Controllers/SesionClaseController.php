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
    // Mostrar panel de sesiones de clase y escáner en vivo (solo asignaturas que imparte este docente)
    public function index(Request $request)
    {
        $profesorId = Auth::id();

        // Cerrar automáticamente las sesiones activas cuyo tiempo de cierre programado ya haya vencido
        $sesionesPorVencer = SesionClase::where('profesor_id', $profesorId)
            ->where('estado', 'activa')
            ->get();

        foreach ($sesionesPorVencer as $sesAct) {
            if ($sesAct->hora_fin) {
                $limiteFin = self::obtenerTimestampFin($sesAct);
                if (Carbon::now()->greaterThanOrEqualTo($limiteFin)) {
                    self::procesarCierreSesion($sesAct, true);
                }
            }
        }

        $materias = Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->orderBy('grupo_id')
            ->get();

        $materiaSeleccionada = $request->query('materia_id');
        
        // Buscar si el profesor tiene una sesión activa en este momento
        $sesionActiva = SesionClase::with(['grupo', 'materia.grupo', 'asistencias.alumno'])
            ->where('profesor_id', $profesorId)
            ->where('estado', 'activa')
            ->first();

        // Historial de sesiones anteriores impartidas por este docente (paginado de 8 en 8)
        $historialSesiones = SesionClase::with(['grupo', 'materia.grupo', 'asistencias.alumno'])
            ->where('profesor_id', $profesorId)
            ->orderBy('created_at', 'desc')
            ->paginate(8, ['*'], 'historial_page')
            ->withQueryString();

        return view('profesor.sesiones', compact('materias', 'sesionActiva', 'historialSesiones', 'materiaSeleccionada'));
    }

    // Abrir una nueva sesión de clase vinculando explícitamente (profesor_id + grupo_id + materia_id)
    public function iniciarSesion(Request $request)
    {
        $request->validate([
            'materia_id'         => 'required|exists:materias,id',
            'tolerancia_minutos' => 'required|integer|min:0|max:60',
            'duracion_minutos'   => 'required|integer|min:1|max:300|gt:tolerancia_minutos',
        ], [
            'duracion_minutos.gt' => 'El tiempo de cierre automático debe ser estrictamente mayor al tiempo de tolerancia.',
            'duracion_minutos.min' => 'El tiempo de cierre automático debe ser de al menos 1 minuto.',
        ]);

        $profesorId = Auth::id();
        $materia = Materia::findOrFail($request->materia_id);

        // Verificar que la asignatura corresponda al docente autenticado
        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) $profesorId) {
            return back()->with('error', 'Solo puedes iniciar sesiones de clase para las asignaturas que te corresponden impartir.');
        }

        // Cerrar cualquier sesión que haya quedado abierta por error de este docente
        $sesionesPrevias = SesionClase::where('profesor_id', $profesorId)
            ->where('estado', 'activa')
            ->get();
        foreach ($sesionesPrevias as $sesPrev) {
            self::procesarCierreSesion($sesPrev);
        }

        $duracionMinutos = (int) $request->duracion_minutos;
        $ahora = Carbon::now();

        // Crear la nueva sesión activa vinculando ProfesorID + GrupoID + MateriaID simultáneamente
        SesionClase::create([
            'profesor_id'        => $profesorId,
            'grupo_id'           => $materia->grupo_id,
            'materia_id'         => $materia->id,
            'fecha'              => $ahora->toDateString(),
            'hora_inicio'        => $ahora->toTimeString(),
            'hora_fin'           => $ahora->copy()->addMinutes($duracionMinutos)->toTimeString(),
            'tolerancia_minutos' => $request->tolerancia_minutos,
            'estado'             => 'activa',
        ]);

        return redirect()->route('sesiones.index')
                         ->with('success', 'Sesión de clase iniciada para la asignatura y grupo seleccionados. Ya puedes escanear códigos QR.');
    }

    // Cerrar la sesión activa y asignar falta a los alumnos inscritos en esa combinación exacta (GrupoID + MateriaID)
    public function finalizarSesion(Request $request, SesionClase $sesion)
    {
        if ((int) $sesion->profesor_id !== (int) Auth::id()) {
            abort(403, 'No tienes permiso para finalizar una sesión de otro docente.');
        }

        $esAutomatico = $request->boolean('automatico');
        self::procesarCierreSesion($sesion, $esAutomatico);

        $mensaje = $esAutomatico
            ? 'El tiempo de la sesión ha finalizado automáticamente. Se asignó falta a los alumnos no escaneados.'
            : 'Sesión finalizada. Se asignó falta automática a los alumnos de este grupo y asignatura no escaneados.';

        return redirect()->route('sesiones.index')->with('success', $mensaje);
    }

    public static function obtenerTimestampFin(SesionClase $sesion): Carbon
    {
        $inicio = Carbon::parse($sesion->fecha . ' ' . $sesion->hora_inicio);
        $fin = Carbon::parse($sesion->fecha . ' ' . $sesion->hora_fin);
        if ($fin->lessThanOrEqualTo($inicio)) {
            $fin->addDay();
        }
        return $fin;
    }

    public static function procesarCierreSesion(SesionClase $sesion, bool $preservarHoraProgramada = false): void
    {
        if ($sesion->estado === 'finalizada') {
            return;
        }

        $sesion->update([
            'estado'   => 'finalizada',
            'hora_fin' => $preservarHoraProgramada && $sesion->hora_fin
                ? $sesion->hora_fin
                : Carbon::now()->toTimeString(),
        ]);

        $grupoId = $sesion->grupo_id ?? optional($sesion->materia)->grupo_id;

        // Buscar todos los alumnos con matrícula 'aceptado' en esta combinación exacta (MateriaID + GrupoID)
        $queryInscritos = Matricula::where('materia_id', $sesion->materia_id)
            ->where('estado', 'aceptado');

        if ($grupoId) {
            $queryInscritos->where('grupo_id', $grupoId);
        }

        $alumnosInscritos = $queryInscritos->pluck('user_id');

        // A los que no tengan asistencia registrada en esta sesión, se les registra 'falta'
        foreach ($alumnosInscritos as $alumnoId) {
            $tieneRegistro = RegistroAsistencia::where('sesion_clase_id', $sesion->id)
                ->where('user_id', $alumnoId)
                ->exists();

            if (!$tieneRegistro) {
                RegistroAsistencia::create([
                    'sesion_clase_id'   => $sesion->id,
                    'user_id'           => $alumnoId,
                    'timestamp_escaneo' => null,
                    'estado_incidencia' => 'falta',
                    'observaciones'     => 'Falta automática al cierre de sesión (Sin escaneo QR)',
                ]);
            }
        }
    }

    // Permitir al profesor modificar manualmente el estado de asistencia de su propia sesión
    public function cambiarEstadoManual(Request $request, RegistroAsistencia $registro)
    {
        $registro->loadMissing(['sesion', 'alumno']);
        if (!$registro->sesion || (int) $registro->sesion->profesor_id !== (int) Auth::id()) {
            abort(403, 'No tienes permiso para modificar asistencias de otro docente.');
        }

        $request->validate([
            'estado_incidencia' => 'required|in:asistencia,retardo,falta,permiso',
        ]);

        $registro->update([
            'estado_incidencia' => $request->estado_incidencia,
            'observaciones'     => 'Ajustado manualmente por el profesor',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'      => 'success',
                'registro_id' => $registro->id,
                'alumno'      => optional($registro->alumno)->name ?? 'Alumno',
                'estado'      => $registro->estado_incidencia,
                'message'     => 'Estado de ' . (optional($registro->alumno)->name ?? 'alumno') . ' actualizado a ' . strtoupper($registro->estado_incidencia) . '.',
            ]);
        }

        return back()->with('success', 'Estado de asistencia actualizado manualmente.');
    }
}
