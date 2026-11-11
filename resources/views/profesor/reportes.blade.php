<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Exportación | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .table-excel thead th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 0.84rem;
            font-weight: 600;
            border-color: #172554;
            white-space: nowrap;
        }
        .table-excel tbody td {
            font-size: 0.85rem;
            vertical-align: middle;
        }
        .table-excel tbody tr:nth-of-type(even) {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}">
                <i class="bi bi-arrow-left-circle me-2"></i>Volver al Panel
            </a>
            <span class="navbar-text text-white fw-bold">Módulo Analítico de Reportes (PDF / Excel CSV)</span>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Filtros y botones de descarga directa -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Filtros y Descarga de Reportes</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('profesor.reportes.csv', ['materia_id' => $materiaId, 'mes' => $mes]) }}" class="btn btn-success rounded-3 fw-semibold">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV (Excel)
                    </a>
                    <a href="{{ route('profesor.reportes.pdf', ['materia_id' => $materiaId, 'mes' => $mes]) }}" class="btn btn-danger rounded-3 fw-semibold">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Descargar Reporte PDF
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('profesor.reportes') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Materia y Grupo</label>
                    <select name="materia_id" class="form-select">
                        <option value="">-- Todas mis materias --</option>
                        @foreach($materias as $mat)
                            <option value="{{ $mat->id }}" {{ $materiaId == $mat->id ? 'selected' : '' }}>
                                {{ $mat->nombre_materia }} ({{ $mat->codigo_materia }})
                                @if(optional($mat->grupo)->nombre_grupo)
                                    — Grupo {{ $mat->grupo->nombre_grupo }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mes</label>
                    <input type="month" name="mes" value="{{ $mes }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                        <i class="bi bi-search me-1"></i>Aplicar Filtro
                    </button>
                    @if($materiaId || $mes)
                        <a href="{{ route('profesor.reportes') }}" class="btn btn-outline-secondary rounded-3 py-2" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @php
            $cntAsistencia = $registros->where('estado_incidencia', 'asistencia')->count();
            $cntRetardo    = $registros->where('estado_incidencia', 'retardo')->count();
            $cntPermiso    = $registros->where('estado_incidencia', 'permiso')->count();
            $cntFalta      = $registros->where('estado_incidencia', 'falta')->count();
        @endphp

        <!-- Tabla de resultados homologada con el reporte de Excel y el PDF descargable -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Reporte Consolidado de Asistencia Escolar (SAE QR)</h4>
                    <p class="text-muted small mb-0">Docente: {{ Auth::user()->name }} | Fecha de consulta: {{ date('Y-m-d H:i') }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-dark fs-6">{{ $registros->count() }} Registros</span>
                    <span class="badge bg-success fs-6">Asistencias: {{ $cntAsistencia }}</span>
                    <span class="badge bg-warning text-dark fs-6">Retardos: {{ $cntRetardo }}</span>
                    <span class="badge bg-info text-dark fs-6">Permisos: {{ $cntPermiso }}</span>
                    <span class="badge bg-danger fs-6">Faltas: {{ $cntFalta }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-excel align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha Sesión</th>
                            <th>Grupo</th>
                            <th>Código Materia</th>
                            <th>Materia</th>
                            <th>Matrícula</th>
                            <th>Alumno</th>
                            <th>Correo</th>
                            <th>Hora Escaneo</th>
                            <th>Estado Incidencia</th>
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
                                $nombreGrupo = optional($reg->sesion->grupo)->nombre_grupo
                                    ?? optional(optional($reg->sesion->materia)->grupo)->nombre_grupo
                                    ?? 'N/A';
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $reg->sesion->fecha ?? '-' }}</td>
                                <td class="fw-semibold text-nowrap">{{ $nombreGrupo }}</td>
                                <td class="text-nowrap">
                                    <span class="badge bg-secondary">{{ $reg->sesion->materia->codigo_materia ?? '-' }}</span>
                                </td>
                                <td>{{ $reg->sesion->materia->nombre_materia ?? '-' }}</td>
                                <td class="text-nowrap">{{ $reg->alumno->matricula_codigo ?? 'S/M' }}</td>
                                <td class="fw-semibold">{{ $reg->alumno->name ?? '-' }}</td>
                                <td class="small text-muted">{{ $reg->alumno->email ?? '-' }}</td>
                                <td class="text-nowrap">{{ $reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $badgeColor }} text-uppercase">{{ $reg->estado_incidencia }}</span>
                                </td>
                                <td class="small">{{ $reg->observaciones ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No hay registros de asistencia para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
