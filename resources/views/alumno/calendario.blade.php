<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Asistencias | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('alumno.dashboard') }}">SAE QR | Calendario</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('alumno.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
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
        <!-- Alerta de límite crítico de faltas y Derecho a Examen Ordinario (RF-14 + Regla Universitaria: 3 retardos = 1 falta) -->
        @if($alertaCritica)
            <div class="alert alert-danger d-flex align-items-center rounded-4 shadow-sm mb-4" role="alert">
                <div>
                    <h6 class="fw-bold mb-1">¡Alerta Crítica: Sin Derecho a Examen Ordinario ({{ $faltasTotales }} / 3 faltas acumuladas)!</h6>
                    <p class="mb-0 small">
                        Has acumulado <strong>{{ $faltasTotales }} faltas computadas</strong> en el periodo seleccionado ({{ $faltas }} directas + {{ $faltasPorRetardos }} por {{ $retardos }} retardos acumulados). Sube tu justificante a la brevedad para recuperar tu derecho a evaluación ordinaria.
                    </p>
                </div>
            </div>
        @elseif(!empty($alertaPreventiva))
            <div class="alert alert-warning d-flex align-items-center rounded-4 shadow-sm mb-4" role="alert">
                <div>
                    <h6 class="fw-bold mb-1">Aviso Preventivo de Derecho a Examen (2 / 3 faltas acumuladas)</h6>
                    <p class="mb-0 small">
                        Llevas <strong>2 faltas computadas</strong> ({{ $faltas }} directas + {{ $faltasPorRetardos }} por {{ $retardos }} retardos). Recuerda que en el reglamento universitario <strong>cada 3 retardos equivalen a 1 falta</strong> y el límite máximo es de 3 faltas.
                    </p>
                </div>
            </div>
        @endif

        <!-- Filtros por Mes y Materia -->
        <div class="card rounded-4 p-4 bg-white mb-4">
            <form method="GET" action="{{ route('alumno.calendario') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mes a consultar</label>
                    <input type="month" name="mes" value="{{ $mesActual }}" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Filtrar por Materia</label>
                    <select name="materia_id" class="form-select">
                        <option value="">Todas mis materias</option>
                        @foreach($misMaterias as $mat)
                            <option value="{{ $mat->materia->id }}" {{ $materiaFiltro == $mat->materia->id ? 'selected' : '' }}>
                                {{ $mat->materia->nombre_materia }} ({{ $mat->materia->codigo_materia }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Resumen con códigos de color -->
        <div class="row g-4 mb-4 text-center">
            <div class="col-6 col-md-3">
                <div class="bento metrica-esmeralda rounded-4 p-3">
                    <div class="numero-bento">{{ $asistencias }}</div>
                    <div class="texto-slate small fw-semibold mt-2">Asistencias</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bento metrica-ambar rounded-4 p-3">
                    <div class="numero-bento">{{ $retardos }}</div>
                    <div class="texto-slate small fw-semibold mt-2">Retardos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bento metrica-cielo rounded-4 p-3">
                    <div class="numero-bento">{{ $permisos }}</div>
                    <div class="texto-slate small fw-semibold mt-2">Permisos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bento metrica-coral rounded-4 p-3">
                    <div class="numero-bento">{{ $faltasTotales }}</div>
                    <div class="texto-slate small fw-semibold mt-2">Faltas Computadas</div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de sesiones del mes (Calendario Visual) -->
        <div class="card rounded-4 p-4 bg-white">
            <h5 class="titulo-panel mb-4">Registro Diario del Mes</h5>

            <div class="row g-3">
                @forelse($registros as $reg)
                    @php
                        $colorBadge = match($reg->estado_incidencia) {
                            'asistencia' => 'badge-asistencia',
                            'retardo'    => 'badge-retardo',
                            'permiso'    => 'badge-permiso',
                            default      => 'badge-falta',
                        };
                        $bordeCard = match($reg->estado_incidencia) {
                            'asistencia' => 'border-success',
                            'retardo'    => 'border-warning',
                            'permiso'    => 'border-info',
                            default      => 'border-danger',
                        };
                    @endphp
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm rounded-4 border-2 {{ $bordeCard }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark"><i class="bi bi-calendar-event me-1"></i>{{ $reg->sesion->fecha }}</span>
                                    <span class="badge badge-estado {{ $colorBadge }} text-uppercase">{{ $reg->estado_incidencia }}</span>
                                </div>
                                <h6 class="fw-bold mb-1">{{ $reg->sesion->materia->nombre_materia }}</h6>
                                <p class="texto-slate small mb-0">Hora pase: {{ $reg->timestamp_escaneo ?? $reg->hora_pase ?? '--:--' }}</p>
                                @if($reg->observaciones)
                                    <p class="small text-secondary mt-2 mb-0 fst-italic">"{{ $reg->observaciones }}"</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center texto-slate py-4">
                        No se encontraron registros de asistencia en este mes.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
