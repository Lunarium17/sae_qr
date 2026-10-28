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

        $rawMes = $request->input('mes');
        $mesActual = (is_string($rawMes) && preg_match('/^\d{4}-\d{2}$/', $rawMes))
            ? $rawMes
            : Carbon::now()->format('Y-m');

        $rawMateria = $request->input('materia_id');
        $materiaFiltro = (is_numeric($rawMateria) && (int) $rawMateria > 0) ? (int) $rawMateria : null;

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

        // Contadores para resumen y alerta de inasistencias (RF-14: máximo 3 faltas sin justificar + Regla Universitaria: cada 3 retardos = 1 falta)
        $totalSesiones     = $registros->count();
        $asistencias       = $registros->where('estado_incidencia', 'asistencia')->count();
        $retardos          = $registros->where('estado_incidencia', 'retardo')->count();
        $permisos          = $registros->where('estado_incidencia', 'permiso')->count();
        $faltas            = $registros->where('estado_incidencia', 'falta')->count();
        $faltasPorRetardos = intdiv($retardos, 3);
        $faltasTotales     = $faltas + $faltasPorRetardos;

        $porcentajeFaltas = $totalSesiones > 0 ? round(($faltasTotales / $totalSesiones) * 100, 1) : 0;
        $alertaCritica    = $faltasTotales >= 3; // Alerta crítica si alcanza o supera el límite de 3 faltas acumuladas (sin derecho a ordinario)
        $alertaPreventiva = $faltasTotales === 2; // Advertencia preventiva al acumular 2 faltas equivalentes

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
            'faltasPorRetardos',
            'faltasTotales',
            'porcentajeFaltas',
            'alertaCritica',
            'alertaPreventiva'
        ));
    }
}