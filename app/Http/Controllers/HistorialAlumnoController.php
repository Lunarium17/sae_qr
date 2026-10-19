<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RegistroAsistencia;
use App\Models\Matricula;
use Carbon\Carbon;

class HistorialAlumnoController extends Controller
{
    public function index(Request $request)
    {
        $alumnoId = Auth::id();
        $mesActual = $request->input('mes', Carbon::now()->format('Y-m'));
        $materiaFiltro = $request->input('materia_id');

        // Materias en las que el alumno está aceptado
        $misMaterias = Matricula::with('materia')
            ->where('user_id', $alumnoId)
            ->where('estado', 'aceptado')
            ->get();

        // Consulta del historial de asistencias del mes seleccionado
        $query = RegistroAsistencia::with(['sesion.materia'])
            ->where('user_id', $alumnoId)
            ->whereHas('sesion', function ($q) use ($mesActual, $materiaFiltro) {
                $q->where('fecha', 'like', $mesActual . '%');
                if ($materiaFiltro) {
                    $q->where('materia_id', $materiaFiltro);
                }
            });

        $registros = $query->get()->sortByDesc(function ($reg) {
            return $reg->sesion->fecha;
        });

        // Contadores para resumen y alerta de inasistencias (RF-14)
        $totalSesiones = $registros->count();
        $asistencias   = $registros->where('estado_incidencia', 'asistencia')->count();
        $retardos      = $registros->where('estado_incidencia', 'retardo')->count();
        $permisos      = $registros->where('estado_incidencia', 'permiso')->count();
        $faltas        = $registros->where('estado_incidencia', 'falta')->count();

        $porcentajeFaltas = $totalSesiones > 0 ? round(($faltas / $totalSesiones) * 100, 1) : 0;
        $alertaCritica    = $porcentajeFaltas >= 20; // Alerta si supera o iguala el 20% de faltas

        return view('alumno.calendario', compact(
            'registros',
            'misMaterias',
            'mesActual',
            'materiaFiltro',
            'totalSesiones',
            'asistencias',
            'retardos',
            'permisos',
            'faltas',
            'porcentajeFaltas',
            'alertaCritica'
        ));
    }
}