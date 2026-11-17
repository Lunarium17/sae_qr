<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Exportación | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .table-moderna thead th {
            background-color: #f8fafc;
            color: #334155;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            padding: .75rem .85rem;
        }
        .table-moderna tbody td {
            font-size: .85rem;
            color: #1e293b;
            border-bottom: 1px solid #eef2f7;
            padding: .7rem .85rem;
            vertical-align: middle;
        }
        .table-moderna tbody tr:hover { background-color: #f8fafc; }
        .badge-estado { border-radius: 50rem; font-weight: 600; text-transform: uppercase; font-size: .68rem; padding: .35rem .65rem; }
        .badge-asistencia { background: #dcfce7; color: #14532d; }
        .badge-retardo { background: #fef9c3; color: #713f12; }
        .badge-permiso { background: #dbeafe; color: #1e3a8a; }
        .badge-falta { background: #fee2e2; color: #7f1d1d; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-text text-white fw-bold">Módulo Analítico de Reportes</span>
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-light btn-sm text-dark fw-semibold rounded-3" href="{{ route('profesor.dashboard') }}">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                </a>
                @include('partials.campana')
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Filtros y botones de descarga directa -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h5 class="fw-bold mb-0">Filtros y Descarga de Reportes</h5>
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
                        <option value="">Todas mis materias</option>
                        @foreach($materias as $mat)
                            <option value="{{ $mat->id }}" {{ $materiaId == $mat->id ? 'selected' : '' }}>
                                {{ $mat->nombre_materia }} ({{ $mat->codigo_materia }})
                                @if(optional($mat->grupo)->nombre_grupo)
                                     · Grupo {{ $mat->grupo->nombre_grupo }}
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
                    <h4 class="fw-bold text-dark mb-0">Reporte Consolidado de Asistencia Escolar</h4>
                    <p class="text-muted small mb-0">Docente: {{ Auth::user()->name }} | Fecha de consulta: {{ date('Y-m-d H:i') }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark border fs-6">{{ $registros->count() }} Registros</span>
                    <span class="badge badge-estado badge-asistencia fs-6">Asistencias: {{ $cntAsistencia }}</span>
                    <span class="badge badge-estado badge-retardo fs-6">Retardos: {{ $cntRetardo }}</span>
                    <span class="badge badge-estado badge-permiso fs-6">Permisos: {{ $cntPermiso }}</span>
                    <span class="badge badge-estado badge-falta fs-6">Faltas: {{ $cntFalta }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-moderna align-middle mb-0">
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
                                $badgeEstado = match($reg->estado_incidencia) {
                                    'asistencia' => 'badge-asistencia',
                                    'retardo'    => 'badge-retardo',
                                    'permiso'    => 'badge-permiso',
                                    default      => 'badge-falta',
                                };
                                $nombreGrupo = optional($reg->sesion->grupo)->nombre_grupo
                                    ?? optional(optional($reg->sesion->materia)->grupo)->nombre_grupo
                                    ?? 'N/A';
                            @endphp
                            <tr>
                                <td class="text-nowrap">{{ $reg->sesion->fecha ?? '-' }}</td>
                                <td class="fw-semibold text-nowrap">{{ $nombreGrupo }}</td>
                                <td class="text-nowrap">
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary">{{ $reg->sesion->materia->codigo_materia ?? '-' }}</span>
                                </td>
                                <td>{{ $reg->sesion->materia->nombre_materia ?? '-' }}</td>
                                <td class="text-nowrap">{{ $reg->alumno->matricula_codigo ?? 'S/M' }}</td>
                                <td class="fw-semibold">{{ $reg->alumno->name ?? '-' }}</td>
                                <td class="small text-muted">{{ $reg->alumno->email ?? '-' }}</td>
                                <td class="text-nowrap">{{ $reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-estado {{ $badgeEstado }}">{{ $reg->estado_incidencia }}</span>
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
