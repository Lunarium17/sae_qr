<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Materia;
use App\Models\RegistroAsistencia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    // Vista principal de reportes con filtros y modo imprimible (PDF)
    public function index(Request $request)
    {
        $profesorId = Auth::id();
        $materiaId  = $request->input('materia_id');
        $mes        = $request->input('mes');

        // Obtenemos el catálogo general de materias (no lleva profesor_id)
        $materias = Materia::all();

        $query = RegistroAsistencia::with(['alumno', 'sesion.materia'])
            ->whereHas('sesion', function ($q) use ($profesorId, $materiaId, $mes) {
                $q->where('profesor_id', $profesorId);
                if ($materiaId) {
                    $q->where('materia_id', $materiaId);
                }
                if ($mes) {
                    $q->where('fecha', 'like', $mes . '%');
                }
            });

        $registros = $query->orderBy('created_at', 'desc')->get();

        return view('profesor.reportes', compact('registros', 'materias', 'materiaId', 'mes'));
    }

    // Descarga directa en formato CSV (Excel)
    public function exportarCsv(Request $request)
    {
        $profesorId = Auth::id();
        $materiaId  = $request->input('materia_id');
        $mes        = $request->input('mes');

        $registros = RegistroAsistencia::with(['alumno', 'sesion.materia'])
            ->whereHas('sesion', function ($q) use ($profesorId, $materiaId, $mes) {
                $q->where('profesor_id', $profesorId);
                if ($materiaId) {
                    $q->where('materia_id', $materiaId);
                }
                if ($mes) {
                    $q->where('fecha', 'like', $mes . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'reporte_asistencias_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($registros) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel reconozca acentos correctamente
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados del CSV
            fputcsv($handle, ['Fecha Sesion', 'Codigo Materia', 'Materia', 'Alumno', 'Correo', 'Hora Escaneo', 'Estado Incidencia', 'Observaciones']);

            foreach ($registros as $reg) {
                fputcsv($handle, [
                    $reg->sesion->fecha,
                    $reg->sesion->materia->codigo_materia,
                    $reg->sesion->materia->nombre_materia,
                    $reg->alumno->name,
                    $reg->alumno->email,
                    $reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--',
                    strtoupper($reg->estado_incidencia),
                    $reg->observaciones ?? ''
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}