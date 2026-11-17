<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Exportación | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('profesor.dashboard') }}">SAE QR | Reportes</a>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-secondary btn-sm fw-semibold rounded-3" href="{{ route('profesor.dashboard') }}">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                </a>
                @include('partials.campana')
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Filtros y botones de descarga directa -->
        <div class="card rounded-4 p-4 bg-white mb-4">
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
        <div class="card rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h4 class="fw-bold mb-0" style="color: #0F172A;">Reporte Consolidado de Asistencia Escolar</h4>
                    <p class="texto-slate small mb-0">Docente: {{ Auth::user()->name }} | Fecha de consulta: {{ date('Y-m-d H:i') }}</p>
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
                                <td class="small texto-slate">{{ $reg->alumno->email ?? '-' }}</td>
                                <td class="text-nowrap">{{ $reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-estado {{ $badgeEstado }}">{{ $reg->estado_incidencia }}</span>
                                </td>
                                <td class="small">{{ $reg->observaciones ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center texto-slate py-4">No hay registros de asistencia para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
