<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .card-hover { transition: transform .15s ease, box-shadow .15s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.12) !important; }
        .card-hover:active { transform: scale(.98); }
        a.card-hover:focus-visible { outline: 3px solid rgba(13,110,253,.35); outline-offset: 2px; }
    </style>
</head>
<body class="bg-light">

    @php
        $alumno = \Illuminate\Support\Facades\Auth::user();
        $userId = \Illuminate\Support\Facades\Auth::id();

        $pendientesCount  = \App\Models\Matricula::where('user_id', $userId)->where('estado', 'pendiente')->count();
        $asistenciasCount = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'asistencia')->count();
        $retardosCount    = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'retardo')->count();
        $faltasCount      = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'falta')->count();
        $permisosCount    = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'permiso')->count();

        // Materias en las que el alumno está aceptado
        $matriculasAceptadas = \App\Models\Matricula::with(['grupo', 'materia'])
            ->where('user_id', $userId)
            ->where('estado', 'aceptado')
            ->get();

        $materiaIdsAceptadas = $matriculasAceptadas->pluck('materia_id')->filter()->toArray();

        // Detectar si existe alguna sesión activa en curso para sus materias donde aún no haya escaneado su QR
        $sesionesActivasPendientes = collect();
        if (!empty($materiaIdsAceptadas)) {
            $sesionesActivasPendientes = \App\Models\SesionClase::with('materia.grupo')
                ->whereIn('materia_id', $materiaIdsAceptadas)
                ->where('estado', 'activa')
                ->whereDoesntHave('asistencias', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->get();
        }

        // Faltas y retardos que aún NO tienen un justificante enviado o cuyo justificante fue rechazado
        $incidenciasPorJustificar = \App\Models\RegistroAsistencia::with(['sesion.materia', 'justificante'])
            ->where('user_id', $userId)
            ->whereIn('estado_incidencia', ['falta', 'retardo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($reg) {
                return !$reg->justificante || $reg->justificante->estado_aprobacion === 'rechazado';
            });

        // Estadísticas por cada materia inscrita y alertas de límite de 3 faltas
        $registrosAlumno = \App\Models\RegistroAsistencia::with('sesion')
            ->where('user_id', $userId)
            ->get();

        $alertasMateriasFaltas = [];
        foreach ($matriculasAceptadas as $mat) {
            if (!$mat->materia) continue;
            $regsMat = $registrosAlumno->filter(fn($r) => $r->sesion && (int) $r->sesion->materia_id === (int) $mat->materia_id);
            $totMat  = $regsMat->count();
            $asistM  = $regsMat->where('estado_incidencia', 'asistencia')->count();
            $retM    = $regsMat->where('estado_incidencia', 'retardo')->count();
            $permM   = $regsMat->where('estado_incidencia', 'permiso')->count();
            $faltM   = $regsMat->where('estado_incidencia', 'falta')->count();
            $efecM   = $asistM + $retM + $permM;

            // Regla Universitaria: Cada 3 retardos acumulados en una misma asignatura equivalen a 1 falta
            $faltasPorRetM  = intdiv($retM, 3);
            $faltasTotalesM = $faltM + $faltasPorRetM;

            $mat->asistencias_mat     = $asistM;
            $mat->retardos_mat        = $retM;
            $mat->permisos_mat        = $permM;
            $mat->faltas_mat          = $faltM;
            $mat->faltas_retardos_mat = $faltasPorRetM;
            $mat->faltas_totales_mat  = $faltasTotalesM;
            $mat->total_sesiones      = $totMat;
            $mat->porcentaje_mat      = $totMat > 0 ? round(($efecM / $totMat) * 100) : null;

            if ($faltasTotalesM >= 2) {
                $alertasMateriasFaltas[] = [
                    'materia'         => $mat->materia->nombre_materia,
                    'grupo'           => $mat->grupo->nombre_grupo ?? '-',
                    'faltas'          => $faltasTotalesM,
                    'faltas_directas' => $faltM,
                    'retardos'        => $retM,
                    'faltas_retardos' => $faltasPorRetM,
                    'critico'         => $faltasTotalesM >= 3,
                ];
            }
        }

        $avHue = abs(crc32($alumno->name ?? 'U')) % 360;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Inicio</a>
            <div class="d-flex align-items-center gap-2">
                @if ($alumno->foto_perfil)
                    <img src="{{ asset('storage/' . $alumno->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,.5);">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 34px; height: 34px; background: hsl({{ $avHue }}, 65%, 45%); font-size: .85rem;">{{ mb_strtoupper(mb_substr($alumno->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('alumno.perfil') }}" class="btn btn-light btn-sm text-primary fw-semibold rounded-3">
                    Mi Perfil
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
        <!-- 1. Tarjeta Superior de Bienvenida y Datos del Estudiante -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h3 class="fw-bold text-dark mb-0">Hola, {{ $alumno->name }}</h3>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-1">
                            {{ $alumno->matricula_codigo ? 'Matrícula: ' . $alumno->matricula_codigo : 'Sin matrícula registrada' }}
                        </span>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('alumno.solicitar') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold">
                        <i class="bi bi-search me-1"></i>Buscar y Solicitar Grupo
                        @if ($pendientesCount > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ $pendientesCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Sección de Avisos e Incidencias del Alumno -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    Centro de Avisos y Recordatorios
                </h5>
                @php
                    $totalAvisos = $sesionesActivasPendientes->count() + count($alertasMateriasFaltas) + $incidenciasPorJustificar->count();
                @endphp
                <span class="badge {{ $totalAvisos > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-1">
                    {{ $totalAvisos > 0 ? $totalAvisos . ' avisos importantes' : 'Todo al día' }}
                </span>
            </div>

            <!-- Aviso A: Sesiones de clase activas en este momento -->
            @foreach ($sesionesActivasPendientes as $sesionAct)
                <div class="alert alert-success border-start border-success border-4 d-flex flex-wrap justify-content-between align-items-center rounded-3 mb-2 py-2">
                    <div>
                        <strong>Clase activa en curso.</strong>
                        La materia <strong>{{ $sesionAct->materia->nombre_materia }}</strong>
                        {{ $sesionAct->materia->grupo ? 'del grupo ' . $sesionAct->materia->grupo->nombre_grupo : '' }}
                        está abierta desde las {{ $sesionAct->hora_inicio }}. <a href="{{ route('alumno.qr') }}" class="fw-semibold">Presenta tu código QR aquí</a>.
                    </div>
                    <span class="badge bg-success">Escaneo pendiente</span>
                </div>
            @endforeach

            <!-- Aviso B: Alertas de Derecho a Examen Ordinario y límite de 3 faltas (3 retardos = 1 falta) -->
            @foreach ($alertasMateriasFaltas as $alertaFalta)
                <div class="alert {{ $alertaFalta['critico'] ? 'alert-danger border-danger' : 'alert-warning border-warning' }} border-start border-4 d-flex flex-wrap justify-content-between align-items-center rounded-3 mb-2 py-2">
                    <div>
                        @if ($alertaFalta['critico'])
                            <strong>Sin derecho a examen ordinario: {{ $alertaFalta['faltas'] }} de 3 faltas.</strong>
                        @else
                            <strong>Aviso preventivo: 2 de 3 faltas.</strong>
                        @endif
                        En <strong>{{ $alertaFalta['materia'] }}</strong>{{ $alertaFalta['grupo'] !== '-' ? ' · Grupo ' . $alertaFalta['grupo'] : '' }}
                        acumulas {{ $alertaFalta['faltas_directas'] }} {{ $alertaFalta['faltas_directas'] === 1 ? 'falta' : 'faltas' }} directa{{ $alertaFalta['faltas_directas'] === 1 ? '' : 's' }}
                        + {{ $alertaFalta['faltas_retardos'] }} por {{ $alertaFalta['retardos'] }} {{ $alertaFalta['retardos'] === 1 ? 'retardo' : 'retardos' }}.
                        @if (!$alertaFalta['critico'])
                            Estás a una falta de perder el derecho a examen ordinario.
                        @endif
                    </div>
                    <a href="{{ route('alumno.justificantes') }}" class="btn btn-sm {{ $alertaFalta['critico'] ? 'btn-danger' : 'btn-dark' }} rounded-3">
                        <i class="bi bi-upload me-1"></i>Ir a Justificantes
                    </a>
                </div>
            @endforeach

            <!-- Aviso C: Faltas o retardos específicos pendientes de justificar -->
            @if ($incidenciasPorJustificar->isNotEmpty())
                <div class="mt-2">
                    <h6 class="fw-bold text-secondary small text-uppercase mb-2">
                        <i class="bi bi-clipboard-check me-1"></i>Inasistencias y retardos que puedes justificar
                    </h6>
                    <div class="list-group list-group-flush border rounded-3">
                        @foreach ($incidenciasPorJustificar->take(5) as $inc)
                            <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                                <div>
                                    <span class="badge {{ $inc->estado_incidencia === 'falta' ? 'bg-danger' : 'bg-warning text-dark' }} text-uppercase me-2">
                                        {{ $inc->estado_incidencia }}
                                    </span>
                                    <strong class="text-dark">{{ $inc->sesion->materia->nombre_materia ?? 'Materia' }}</strong>
                                    <span class="text-muted small ms-2">
                                        <i class="bi bi-calendar-event me-1"></i>Fecha: {{ $inc->sesion->fecha ?? '-' }}
                                    </span>
                                    @if ($inc->justificante && $inc->justificante->estado_aprobacion === 'rechazado')
                                        <span class="badge bg-secondary ms-1">Justificante anterior rechazado</span>
                                    @endif
                                </div>
                                <a href="{{ route('alumno.justificantes', ['registro_id' => $inc->id]) }}" class="btn btn-sm btn-outline-primary rounded-3">
                                    <i class="bi bi-send-check me-1"></i>Justificar ahora
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($totalAvisos === 0)
                <div class="text-center text-muted py-2">
                    No tienes faltas pendientes por justificar ni alertas de asistencia en este momento.
                </div>
            @endif
        </div>

        <!-- 3. Resumen Global de Asistencia (mosaicos clicables) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-success shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 me-3">
                                <i class="bi bi-check-circle-fill fs-3"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Asistencias</div>
                                <h3 class="fw-bold text-success mb-0">{{ $asistenciasCount }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-3">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-warning shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 me-3">
                                <i class="bi bi-clock-fill fs-3"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Retardos</div>
                                <h3 class="fw-bold text-warning mb-0">{{ $retardosCount }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-3">
                <a href="{{ route('alumno.justificantes') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-danger shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-4 p-3 me-3">
                                <i class="bi bi-x-circle-fill fs-3"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Faltas sin justificar</div>
                                <h3 class="fw-bold text-danger mb-0">{{ $faltasCount }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-3">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-primary shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 me-3">
                                <i class="bi bi-file-earmark-check fs-3"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Permisos y justificados</div>
                                <h3 class="fw-bold text-primary mb-0">{{ $permisosCount }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 4. Accesos por Sección -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3">Secciones</h5>
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('alumno.qr') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                                <i class="bi bi-qr-code fs-3"></i>
                            </div>
                            @if ($sesionesActivasPendientes->count() > 0)<span class="badge bg-success">Clase activa</span>@endif
                        </div>
                        <h5 class="fw-bold text-dark">Mi Código QR</h5>
                        <p class="text-muted small mb-2">Preséntalo en tu clase en curso para registrar tu asistencia.</p>
                        <span class="text-primary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('alumno.materias') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-dark bg-opacity-10 text-dark rounded-4 p-3">
                                <i class="bi bi-journal-bookmark fs-3"></i>
                            </div>
                            <span class="badge bg-primary">{{ $matriculasAceptadas->count() }}</span>
                        </div>
                        <h5 class="fw-bold text-dark">Mis Materias</h5>
                        <p class="text-muted small mb-2">Consulta tu registro de asistencia y derecho a examen ordinario.</p>
                        <span class="text-dark small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('alumno.calendario') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                                <i class="bi bi-calendar3 fs-3"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark">Calendario</h5>
                        <p class="text-muted small mb-2">Historial de asistencias, retardos y faltas del mes actual.</p>
                        <span class="text-success small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('alumno.justificantes') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3">
                                <i class="bi bi-upload fs-3"></i>
                            </div>
                            @if ($incidenciasPorJustificar->count() > 0)<span class="badge bg-danger">{{ $incidenciasPorJustificar->count() }}</span>@endif
                        </div>
                        <h5 class="fw-bold text-dark">Justificantes</h5>
                        <p class="text-muted small mb-2">Sube tu comprobante para justificar faltas y retardos ante el docente.</p>
                        <span class="text-warning small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('alumno.solicitar') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                                <i class="bi bi-search fs-3"></i>
                            </div>
                            @if ($pendientesCount > 0)<span class="badge bg-warning text-dark">{{ $pendientesCount }}</span>@endif
                        </div>
                        <h5 class="fw-bold text-dark">Solicitar Asignatura</h5>
                        <p class="text-muted small mb-2">Busca la materia y envía tu solicitud de inscripción al docente.</p>
                        <span class="text-info small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('alumno.perfil') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                                <i class="bi bi-person-gear fs-3"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark">Mi Perfil</h5>
                        <p class="text-muted small mb-2">Actualiza tu información de contacto, matrícula y foto de perfil.</p>
                        <span class="text-secondary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
