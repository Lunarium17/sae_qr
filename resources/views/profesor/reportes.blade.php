<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Exportación | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}">
                <i class="bi bi-arrow-left-circle me-2"></i>Volver al Panel
            </a>
            <span class="navbar-text text-white fw-bold">Módulo Analítico de Reportes (PDF / CSV)</span>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Filtros y botones de exportación -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 no-print">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Filtros de Exportación</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('profesor.reportes.csv', ['materia_id' => $materiaId, 'mes' => $mes]) }}" class="btn btn-success rounded-3">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV (Excel)
                    </a>
                    <button onclick="window.print()" class="btn btn-danger rounded-3">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Imprimir / Guardar PDF
                    </button>
                </div>
            </div>

            <form method="GET" action="{{ route('profesor.reportes') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Materia</label>
                    <select name="materia_id" class="form-select">
                        <option value="">-- Todas mis materias --</option>
                        @foreach($materias as $mat)
                            <option value="{{ $mat->id }}" {{ $materiaId == $mat->id ? 'selected' : '' }}>
                                {{ $mat->nombre_materia }} ({{ $mat->codigo_materia }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mes</label>
                    <input type="month" name="mes" value="{{ $mes }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                        <i class="bi bi-search me-1"></i>Aplicar Filtro
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de resultados (Imprimible en PDF) -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Reporte Consolidado de Asistencia Escolar (SAE QR)</h4>
                    <p class="text-muted small mb-0">Docente: {{ Auth::user()->name }} | Fecha de emisión: {{ date('Y-m-d H:i') }}</p>
                </div>
                <span class="badge bg-dark fs-6">{{ $registros->count() }} Registros</span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Materia</th>
                            <th>Alumno</th>
                            <th>Hora Pase</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $reg)
                            @php
                                $badgeColor = match($reg->estado_incidencia) {
                                    'asistencia' => 'success',
                                    'retardo'    => 'warning text-dark',
                                    'permiso'    => 'info text-dark',
                                    default      => 'danger',
                                };
                            @endphp
                            <tr>
                                <td>{{ $reg->sesion->fecha }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $reg->sesion->materia->codigo_materia }}</span>
                                    {{ $reg->sesion->materia->nombre_materia }}
                                </td>
                                <td class="fw-semibold">{{ $reg->alumno->name }}</td>
                                <td>{{ $reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--' }}</td>
                                <td>
                                    <span class="badge bg-{{ $badgeColor }} text-uppercase">{{ $reg->estado_incidencia }}</span>
                                </td>
                                <td class="small">{{ $reg->observaciones ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay registros de asistencia para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>