<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Materia;
use App\Models\RegistroAsistencia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    // Vista principal de reportes con filtros por Asignatura+Grupo del docente
    public function index(Request $request)
    {
        [$registros, $materias, $materiaId, $mes] = $this->obtenerDatosReporte($request);

        return view('profesor.reportes', compact('registros', 'materias', 'materiaId', 'mes'));
    }

    // Descarga directa en formato CSV (Excel) con protección contra CSV Formula Injection
    public function exportarCsv(Request $request)
    {
        [$registros] = $this->obtenerDatosReporte($request);

        $filename = 'reporte_asistencias_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($registros) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel reconozca acentos correctamente
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados del CSV (incluyendo Grupo, Asignatura y Matrícula)
            fputcsv($handle, [
                'Fecha Sesion',
                'Grupo',
                'Codigo Materia',
                'Materia',
                'Matricula',
                'Alumno',
                'Correo',
                'Hora Escaneo',
                'Estado Incidencia',
                'Observaciones',
            ]);

            foreach ($registros as $reg) {
                $nombreGrupo = optional($reg->sesion->grupo)->nombre_grupo
                    ?? optional(optional($reg->sesion->materia)->grupo)->nombre_grupo
                    ?? 'N/A';

                fputcsv($handle, [
                    $this->sanitizarCeldaCsv($reg->sesion->fecha ?? ''),
                    $this->sanitizarCeldaCsv($nombreGrupo),
                    $this->sanitizarCeldaCsv($reg->sesion->materia->codigo_materia ?? ''),
                    $this->sanitizarCeldaCsv($reg->sesion->materia->nombre_materia ?? ''),
                    $this->sanitizarCeldaCsv($reg->alumno->matricula_codigo ?? 'S/M'),
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

    // Descarga directa en formato PDF tabular (estilo hoja de cálculo / Excel)
    public function exportarPdf(Request $request)
    {
        [$registros, $materias, $materiaId, $mes] = $this->obtenerDatosReporte($request);

        $materiaFiltroTexto = 'Todas las materias impartidas';
        if ($materiaId) {
            $matObj = $materias->firstWhere('id', $materiaId);
            if ($matObj) {
                $grupoTxt = optional($matObj->grupo)->nombre_grupo ? ' - Grupo ' . $matObj->grupo->nombre_grupo : '';
                $materiaFiltroTexto = $matObj->nombre_materia . ' (' . $matObj->codigo_materia . ')' . $grupoTxt;
            }
        }
        $mesFiltroTexto = $mes ?: 'Todos los periodos';
        $docenteNombre  = Auth::user()->name ?? 'Docente';

        $pdfBinario = $this->construirPdfTabular($registros, $docenteNombre, $materiaFiltroTexto, $mesFiltroTexto);
        $filename   = 'reporte_asistencias_' . date('Ymd_His') . '.pdf';

        return response($pdfBinario, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($pdfBinario),
        ]);
    }

    // Consulta reutilizable para la vista, el CSV y el PDF
    private function obtenerDatosReporte(Request $request): array
    {
        $profesorId = Auth::id();

        $rawMateriaId = $request->input('materia_id');
        $materiaId    = (is_numeric($rawMateriaId) && (int) $rawMateriaId > 0) ? (int) $rawMateriaId : null;

        $rawMes = $request->input('mes');
        $mes    = (is_string($rawMes) && preg_match('/^\d{4}-\d{2}$/', $rawMes)) ? $rawMes : null;

        $materias = Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->orderBy('grupo_id')
            ->get();

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

        return [$registros, $materias, $materiaId, $mes];
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

    // Generador nativo PDF 1.4 horizontal (Landscape) con diseño de tabla tipo Excel
    private function construirPdfTabular($registros, string $docente, string $materiaFiltro, string $mesFiltro): string
    {
        $pageW      = 792.0; // Tamaño Carta Horizontal (11 in)
        $pageH      = 612.0; // 8.5 in
        $marginL    = 26.0;
        $tableWidth = $pageW - ($marginL * 2); // 740 pt

        // Definición de columnas idénticas al reporte de Excel
        $cols = [
            ['title' => 'Fecha Sesión', 'w' => 58.0,  'chars' => 11],
            ['title' => 'Grupo',        'w' => 56.0,  'chars' => 11],
            ['title' => 'Cód. Mat.',    'w' => 54.0,  'chars' => 10],
            ['title' => 'Materia',      'w' => 112.0, 'chars' => 23],
            ['title' => 'Matrícula',    'w' => 58.0,  'chars' => 11],
            ['title' => 'Alumno',       'w' => 112.0, 'chars' => 23],
            ['title' => 'Correo',       'w' => 112.0, 'chars' => 24],
            ['title' => 'Hora Escaneo', 'w' => 62.0,  'chars' => 12],
            ['title' => 'Estado',       'w' => 52.0,  'chars' => 10],
            ['title' => 'Observaciones','w' => 64.0,  'chars' => 13],
        ];

        $totalReg    = $registros->count();
        $cntAsis     = $registros->where('estado_incidencia', 'asistencia')->count();
        $cntRet      = $registros->where('estado_incidencia', 'retardo')->count();
        $cntPerm     = $registros->where('estado_incidencia', 'permiso')->count();
        $cntFalta    = $registros->where('estado_incidencia', 'falta')->count();
        $fechaActual = date('Y-m-d H:i');

        $pages = [];
        $cmds  = [];
        $y     = 0.0;

        $encode = function (?string $txt): string {
            $s = (string) $txt;
            $w = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
            if ($w === false) {
                $w = $s;
            }
            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $w);
        };

        $wrap = function (?string $txt, int $maxChars): array {
            $clean = trim(preg_replace('/\s+/', ' ', (string) $txt));
            if ($clean === '') {
                return ['-'];
            }
            $words = explode(' ', $clean);
            $lines = [];
            $cur   = '';
            foreach ($words as $word) {
                while (mb_strlen($word) > $maxChars) {
                    if ($cur !== '') {
                        $lines[] = $cur;
                        $cur = '';
                    }
                    $lines[] = mb_substr($word, 0, $maxChars);
                    $word    = mb_substr($word, $maxChars);
                }
                if ($cur === '') {
                    $cur = $word;
                } elseif (mb_strlen($cur . ' ' . $word) <= $maxChars) {
                    $cur .= ' ' . $word;
                } else {
                    $lines[] = $cur;
                    $cur     = $word;
                }
            }
            if ($cur !== '') {
                $lines[] = $cur;
            }
            return $lines ?: ['-'];
        };

        $drawTableHeader = function () use (&$cmds, &$y, $marginL, $tableWidth, $cols, $encode) {
            $hdrH   = 20.0;
            $bottom = $y - $hdrH;
            // Fondo azul marino estilo encabezado de tabla de Excel
            $cmds[] = sprintf('0.12 0.23 0.54 rg %.2f %.2f %.2f %.2f re f', $marginL, $bottom, $tableWidth, $hdrH);
            $cmds[] = sprintf('0.10 0.18 0.42 RG 0.6 w %.2f %.2f %.2f %.2f re S', $marginL, $bottom, $tableWidth, $hdrH);

            $curX = $marginL;
            foreach ($cols as $idx => $col) {
                if ($idx > 0) {
                    $cmds[] = sprintf('0.25 0.38 0.68 RG 0.4 w %.2f %.2f m %.2f %.2f l S', $curX, $bottom, $curX, $y);
                }
                $cmds[] = sprintf(
                    'BT /F2 7.5 Tf 1 1 1 rg %.2f %.2f Td (%s) Tj ET',
                    $curX + 3.5,
                    $bottom + 6.5,
                    $encode($col['title'])
                );
                $curX += $col['w'];
            }
            $y = $bottom;
        };

        $startPage = function (bool $isFirstPage) use (
            &$pages, &$cmds, &$y, $pageH, $marginL, $tableWidth, $encode,
            $docente, $materiaFiltro, $mesFiltro, $fechaActual,
            $totalReg, $cntAsis, $cntRet, $cntPerm, $cntFalta, $drawTableHeader
        ) {
            if (!empty($cmds)) {
                $pages[] = implode("\n", $cmds);
            }
            $cmds = [];
            $y    = $pageH - 24.0;

            if ($isFirstPage) {
                // Encabezado institucional en la primera página
                $bannerH = 42.0;
                $bannerB = $y - $bannerH;
                $cmds[]  = sprintf('0.09 0.20 0.46 rg %.2f %.2f %.2f %.2f re f', $marginL, $bannerB, $tableWidth, $bannerH);
                $cmds[]  = sprintf(
                    'BT /F2 12.5 Tf 1 1 1 rg %.2f %.2f Td (%s) Tj ET',
                    $marginL + 10.0,
                    $y - 16.0,
                    $encode('REPORTE TABULAR DE ASISTENCIAS ESCOLARES — SAE QR')
                );
                $cmds[]  = sprintf(
                    'BT /F1 8.2 Tf 0.86 0.92 1 rg %.2f %.2f Td (%s) Tj ET',
                    $marginL + 10.0,
                    $y - 31.0,
                    $encode("Docente: {$docente}   |   Asignatura: {$materiaFiltro}   |   Mes: {$mesFiltro}   |   Emisión: {$fechaActual}")
                );
                $y = $bannerB - 6.0;

                // Barra de resumen estadístico (estilo cinta superior de Excel)
                $kpiH = 18.0;
                $kpiB = $y - $kpiH;
                $cmds[] = sprintf('0.94 0.96 0.99 rg 0.78 0.83 0.90 RG 0.5 w %.2f %.2f %.2f %.2f re B', $marginL, $kpiB, $tableWidth, $kpiH);
                $resumenTxt = "Total Registros: {$totalReg}     |     Asistencias: {$cntAsis}     |     Retardos: {$cntRet}     |     Permisos / Justificados: {$cntPerm}     |     Faltas: {$cntFalta}";
                $cmds[] = sprintf(
                    'BT /F2 8 Tf 0.12 0.20 0.35 rg %.2f %.2f Td (%s) Tj ET',
                    $marginL + 8.0,
                    $kpiB + 5.5,
                    $encode($resumenTxt)
                );
                $y = $kpiB - 8.0;
            } else {
                // Encabezado compacto en páginas subsecuentes
                $cmds[] = sprintf(
                    'BT /F2 8.5 Tf 0.20 0.28 0.40 rg %.2f %.2f Td (%s) Tj ET',
                    $marginL,
                    $y - 10.0,
                    $encode("Reporte de Asistencias SAE QR (Continuación) — {$materiaFiltro}")
                );
                $y -= 16.0;
            }

            $drawTableHeader();
        };

        $startPage(true);

        if ($registros->isEmpty()) {
            $rowH   = 26.0;
            $bottom = $y - $rowH;
            $cmds[] = sprintf('1 1 1 rg 0.78 0.82 0.87 RG 0.5 w %.2f %.2f %.2f %.2f re B', $marginL, $bottom, $tableWidth, $rowH);
            $cmds[] = sprintf(
                'BT /F1 8.5 Tf 0.40 0.45 0.52 rg %.2f %.2f Td (%s) Tj ET',
                $marginL + 220.0,
                $bottom + 9.0,
                $encode('No existen registros de asistencia para los filtros seleccionados.')
            );
            $y = $bottom;
        } else {
            foreach ($registros as $rowIndex => $reg) {
                $nombreGrupo = optional($reg->sesion->grupo)->nombre_grupo
                    ?? optional(optional($reg->sesion->materia)->grupo)->nombre_grupo
                    ?? 'N/A';

                $horaEscaneo = $reg->timestamp_escaneo
                    ? date('H:i:s', strtotime($reg->timestamp_escaneo))
                    : ($reg->hora_pase ?? '--:--');

                $estadoUpper = strtoupper($reg->estado_incidencia ?? 'SIN_REGISTRO');

                $rawCells = [
                    $reg->sesion->fecha ?? '-',
                    $nombreGrupo,
                    $reg->sesion->materia->codigo_materia ?? '-',
                    $reg->sesion->materia->nombre_materia ?? '-',
                    $reg->alumno->matricula_codigo ?? 'S/M',
                    $reg->alumno->name ?? '-',
                    $reg->alumno->email ?? '-',
                    $horaEscaneo,
                    $estadoUpper,
                    $reg->observaciones ?? '-',
                ];

                $wrappedCells = [];
                $maxLines     = 1;
                foreach ($cols as $cIdx => $col) {
                    $lines = $wrap($rawCells[$cIdx], $col['chars']);
                    $wrappedCells[$cIdx] = $lines;
                    if (count($lines) > $maxLines) {
                        $maxLines = count($lines);
                    }
                }

                $lineH = 8.8;
                $rowH  = ($maxLines * $lineH) + 6.0;

                if ($y - $rowH < 36.0) {
                    $startPage(false);
                }

                $bottom = $y - $rowH;

                // Fondo cebreado de fila (blanco / gris muy tenue tipo Excel)
                if ($rowIndex % 2 === 1) {
                    $cmds[] = sprintf('0.965 0.975 0.99 rg %.2f %.2f %.2f %.2f re f', $marginL, $bottom, $tableWidth, $rowH);
                } else {
                    $cmds[] = sprintf('1 1 1 rg %.2f %.2f %.2f %.2f re f', $marginL, $bottom, $tableWidth, $rowH);
                }

                // Celda coloreada para la columna Estado (índice 8)
                $estadoX = $marginL;
                for ($i = 0; $i < 8; $i++) {
                    $estadoX += $cols[$i]['w'];
                }
                $estadoW = $cols[8]['w'];
                [$bgR, $bgG, $bgB, $txtR, $txtG, $txtB] = match ($reg->estado_incidencia) {
                    'asistencia' => [0.86, 0.98, 0.90, 0.08, 0.45, 0.22],
                    'retardo'    => [0.99, 0.95, 0.78, 0.55, 0.35, 0.04],
                    'permiso'    => [0.85, 0.95, 0.99, 0.05, 0.38, 0.55],
                    default      => [0.99, 0.88, 0.88, 0.65, 0.12, 0.12],
                };
                $cmds[] = sprintf('%.2f %.2f %.2f rg %.2f %.2f %.2f %.2f re f', $bgR, $bgG, $bgB, $estadoX, $bottom, $estadoW, $rowH);

                // Bordes de la fila y líneas divisorias verticales de cada celda
                $cmds[] = sprintf('0.78 0.82 0.87 RG 0.4 w %.2f %.2f %.2f %.2f re S', $marginL, $bottom, $tableWidth, $rowH);
                $curX   = $marginL;
                foreach ($cols as $cIdx => $col) {
                    if ($cIdx > 0) {
                        $cmds[] = sprintf('0.82 0.85 0.90 RG 0.35 w %.2f %.2f m %.2f %.2f l S', $curX, $bottom, $curX, $y);
                    }
                    $font = ($cIdx === 5 || $cIdx === 8) ? '/F2' : '/F1';
                    $r    = ($cIdx === 8) ? $txtR : 0.12;
                    $g    = ($cIdx === 8) ? $txtG : 0.16;
                    $b    = ($cIdx === 8) ? $txtB : 0.23;

                    $lineY = $y - 9.2;
                    foreach ($wrappedCells[$cIdx] as $ln) {
                        $cmds[] = sprintf(
                            'BT %s 7.1 Tf %.2f %.2f %.2f rg %.2f %.2f Td (%s) Tj ET',
                            $font,
                            $r,
                            $g,
                            $b,
                            $curX + 3.0,
                            $lineY,
                            $encode($ln)
                        );
                        $lineY -= $lineH;
                    }
                    $curX += $col['w'];
                }

                $y = $bottom;
            }
        }

        if (!empty($cmds)) {
            $pages[] = implode("\n", $cmds);
        }

        // Empaquetado de objetos PDF 1.4
        $totalPages = count($pages);
        $objects    = [];
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $kids  = [];
        $objId = 5;
        foreach ($pages as $idx => $content) {
            $pageNum = $idx + 1;
            $footer  = sprintf(
                "\n0.82 0.85 0.88 RG 0.4 w %.2f 24 m %.2f 24 l S\nBT /F1 7.5 Tf 0.42 0.47 0.54 rg %.2f 13 Td (%s) Tj ET",
                $marginL,
                $pageW - $marginL,
                $marginL,
                $encode("SAE QR — Reporte Oficial de Asistencias   |   Página {$pageNum} de {$totalPages}")
            );
            $stream       = $content . $footer;
            $pageObjId    = $objId++;
            $contentObjId = $objId++;
            $kids[]       = $pageObjId . ' 0 R';

            $objects[$contentObjId] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
            $objects[$pageObjId]    = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.0f %.0f] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                $pageW,
                $pageH,
                $contentObjId
            );
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        ksort($objects);

        $pdf     = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $totalObjs  = count($objects) + 1;
        $pdf .= "xref\n0 {$totalObjs}\n0000000000 65535 f \n";
        for ($i = 1; $i < $totalObjs; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size {$totalObjs} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
