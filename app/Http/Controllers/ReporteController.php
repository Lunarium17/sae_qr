<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Materia;
use App\Models\RegistroAsistencia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    // Vista principal de reportes con filtros por Asignatura+Grupo del docente y modo imprimible (PDF)
    public function index(Request $request)
    {
        $profesorId = Auth::id();

        $rawMateriaId = $request->input('materia_id');
        $materiaId    = (is_numeric($rawMateriaId) && (int) $rawMateriaId > 0) ? (int) $rawMateriaId : null;

        $rawMes = $request->input('mes');
        $mes    = (is_string($rawMes) && preg_match('/^\d{4}-\d{2}$/', $rawMes)) ? $rawMes : null;

        // Catálogo de asignaturas impartidas por este docente
        $materias = Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->orderBy('grupo_id')
            ->get();

        $query = RegistroAsistencia::with(['alumno', 'sesion.grupo', 'sesion.materia.grupo'])
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

    // Descarga directa en formato CSV (Excel) con protección contra CSV Formula Injection
    public function exportarCsv(Request $request)
    {
        $profesorId = Auth::id();

        $rawMateriaId = $request->input('materia_id');
        $materiaId    = (is_numeric($rawMateriaId) && (int) $rawMateriaId > 0) ? (int) $rawMateriaId : null;

        $rawMes = $request->input('mes');
        $mes    = (is_string($rawMes) && preg_match('/^\d{4}-\d{2}$/', $rawMes)) ? $rawMes : null;

        $registros = RegistroAsistencia::with(['alumno', 'sesion.grupo', 'sesion.materia.grupo'])
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

            // Encabezados del CSV (incluyendo Grupo y Asignatura)
            fputcsv($handle, ['Fecha Sesion', 'Grupo', 'Codigo Materia', 'Materia', 'Alumno', 'Correo', 'Hora Escaneo', 'Estado Incidencia', 'Observaciones']);

            foreach ($registros as $reg) {
                $nombreGrupo = optional($reg->sesion->grupo)->nombre_grupo
                    ?? optional(optional($reg->sesion->materia)->grupo)->nombre_grupo
                    ?? 'N/A';

                fputcsv($handle, [
                    $this->sanitizarCeldaCsv($reg->sesion->fecha ?? ''),
                    $this->sanitizarCeldaCsv($nombreGrupo),
                    $this->sanitizarCeldaCsv($reg->sesion->materia->codigo_materia ?? ''),
                    $this->sanitizarCeldaCsv($reg->sesion->materia->nombre_materia ?? ''),
                    $this->sanitizarCeldaCsv($reg->alumno->name ?? ''),
                    $this->sanitizarCeldaCsv($reg->alumno->email ?? ''),
                    $this->sanitizarCeldaCsv($reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--'),
                    $this->sanitizarCeldaCsv(strtoupper($reg->estado_incidencia ?? '')),
                    $this->sanitizarCeldaCsv($reg->observaciones ?? ''),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Evita que Excel ejecute fórmulas si una celda inicia con =, +, -, @, tab o retorno de carro
    private function sanitizarCeldaCsv(?string $valor): string
    {
        $texto = (string) $valor;
        if ($texto !== '' && in_array($texto[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $texto;
        }
        return $texto;
    }
}
