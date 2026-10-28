<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Alumno - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-person-badge me-2"></i>SAE QR | Panel de Alumno</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('alumno.perfil') }}" class="btn btn-light btn-sm text-primary fw-semibold rounded-3">
                    <i class="bi bi-person-gear me-1"></i>Mi Perfil
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm rounded-3">Cerrar Sesión</button>
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
                            <i class="bi bi-card-heading me-1"></i>{{ $alumno->matricula_codigo ? 'Matrícula: ' . $alumno->matricula_codigo : 'Sin matrícula registrada' }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-envelope me-1"></i>{{ $alumno->email }} — Consulta tus materias inscritas, avisos de asistencia y presenta tu código QR temporal en clase.
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('alumno.solicitar') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold">
                        <i class="bi bi-search me-1"></i>Buscar y Solicitar Grupo
                        @if ($pendientesCount > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ $pendientesCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('alumno.perfil') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3 py-2">
                        <i class="bi bi-pencil-square me-1"></i>Ver / Editar Mis Datos
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Sección de Avisos e Incidencias del Alumno -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-bell-fill me-2 text-warning"></i>Centro de Avisos y Recordatorios
                </h5>
                @php
                    $totalAvisos = $sesionesActivasPendientes->count() + count($alertasMateriasFaltas) + $incidenciasPorJustificar->count();
                @endphp
                <span class="badge {{ $totalAvisos > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-1">
                    {{ $totalAvisos > 0 ? $totalAvisos . ' aviso(s) importante(s)' : 'Todo al día' }}
                </span>
            </div>

            <!-- Aviso A: Sesiones de clase activas en este momento -->
            @foreach ($sesionesActivasPendientes as $sesionAct)
                <div class="alert alert-success border-start border-success border-4 d-flex flex-wrap justify-content-between align-items-center rounded-3 mb-2 py-2">
                    <div>
                        <i class="bi bi-broadcast me-2"></i>
                        <strong>¡Clase Activa en Curso!</strong> La materia <strong>{{ $sesionAct->materia->nombre_materia }}</strong>
                        {{ $sesionAct->materia->grupo ? '(' . $sesionAct->materia->grupo->nombre_grupo . ')' : '' }}
                        está abierta desde las {{ $sesionAct->hora_inicio }}. Presenta tu código QR de abajo a tu profesor.
                    </div>
                    <span class="badge bg-success">Escaneo Pendiente</span>
                </div>
            @endforeach

            <!-- Aviso B: Alertas de Derecho a Examen Ordinario y límite de 3 faltas (3 retardos = 1 falta) -->
            @foreach ($alertasMateriasFaltas as $alertaFalta)
                <div class="alert {{ $alertaFalta['critico'] ? 'alert-danger border-danger' : 'alert-warning border-warning' }} border-start border-4 d-flex flex-wrap justify-content-between align-items-center rounded-3 mb-2 py-2">
                    <div>
                        <i class="bi {{ $alertaFalta['critico'] ? 'bi-exclamation-octagon-fill' : 'bi-exclamation-triangle-fill' }} me-2"></i>
                        @if ($alertaFalta['critico'])
                            <strong>¡Sin Derecho a Examen Ordinario ({{ $alertaFalta['faltas'] }} / 3 faltas)!</strong> En <strong>{{ $alertaFalta['materia'] }}</strong> (Grupo {{ $alertaFalta['grupo'] }}) acumulas {{ $alertaFalta['faltas_directas'] }} falta(s) directa(s) + {{ $alertaFalta['faltas_retardos'] }} por {{ $alertaFalta['retardos'] }} retardo(s).
                        @else
                            <strong>Aviso Preventivo (2 / 3 faltas):</strong> Llevas 2 faltas computadas en <strong>{{ $alertaFalta['materia'] }}</strong> (Grupo {{ $alertaFalta['grupo'] }}: {{ $alertaFalta['faltas_directas'] }} directa(s) + {{ $alertaFalta['faltas_retardos'] }} por {{ $alertaFalta['retardos'] }} retardo(s)). Estás a 1 falta de perder derecho a examen ordinario.
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
                        <i class="bi bi-file-earmark-medical me-1"></i>Inasistencias y Retardos que puedes justificar:
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
                    <i class="bi bi-check-circle-fill text-success me-1"></i>No tienes faltas pendientes por justificar ni alertas de asistencia en este momento.
                </div>
            @endif
        </div>

        <!-- 3. Código QR Temporal + Resumen General de Asistencia -->
        <div class="row g-4 mb-4">
            <!-- Columna del Código QR Dinámico -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                    <h5 class="text-dark mb-3 fw-bold">Mi Código QR Temporal</h5>

                    <!-- Confirmación en Tiempo Real cuando el QR del Alumno es escaneado -->
                    <div id="qr-confirmacion-escaneo" data-estado-url="{{ route('qr.estado') }}" class="alert alert-success border-success border-2 rounded-4 py-2 px-3 mb-3 d-none shadow-sm text-start">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-patch-check-fill fs-3 text-success me-2"></i>
                            <div class="small">
                                <div class="fw-bold text-success">¡Pase de Lista Confirmado Hoy!</div>
                                <div id="qr-confirmacion-detalle" class="text-dark"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-white border rounded-4 d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 246px; height: 246px;">
                        <div id="qrcode" data-qr-url="{{ route('qr.generar') }}"></div>
                    </div>

                    <div id="qr-error-sesion" class="alert alert-warning small py-2 px-3 d-none mb-2">
                        Tu sesión de Alumno cambió porque iniciaste sesión como Profesor en otra pestaña del mismo navegador. Abre el perfil de Profesor o Alumno en una <strong>ventana de incógnito</strong> o en otro dispositivo.
                    </div>

                    <div class="mb-2">
                        <span id="qr-timer" class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
                            Actualizando en 60s
                        </span>
                    </div>

                    <button onclick="generarNuevoQR()" class="btn btn-sm btn-outline-secondary rounded-pill mx-auto px-3 mt-1">
                        <i class="bi bi-arrow-clockwise me-1"></i>Renovar QR ahora
                    </button>

                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted d-block mb-1">Código de respaldo (para pruebas en la misma PC):</small>
                        <code id="qr-token-respaldo" class="user-select-all bg-light px-2 py-1 rounded border small text-dark">—</code>
                    </div>

                    <p class="text-muted small mt-2 mb-0">Presenta esta pantalla frente a la cámara de tu profesor durante la clase activa.</p>
                </div>
            </div>
            
            <!-- Columna de Resumen de Asistencia -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="text-dark fw-bold mb-4">Resumen Global de Asistencia</h5>

                    <ul class="list-group list-group-flush fs-5">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Asistencias registradas</span>
                            <span class="badge bg-success rounded-pill px-3">{{ $asistenciasCount }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-warning"><i class="bi bi-clock-fill me-2"></i>Retardos</span>
                            <span class="badge bg-warning text-dark rounded-pill px-3">{{ $retardosCount }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Faltas sin justificar</span>
                            <span class="badge bg-danger rounded-pill px-3">{{ $faltasCount }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-primary"><i class="bi bi-info-circle-fill me-2"></i>Permiso / Justificado</span>
                            <span class="badge bg-primary rounded-pill px-3">{{ $permisosCount }}</span>
                        </li>
                    </ul>

                    <hr class="my-4">
                    <div class="d-grid gap-3">
                        <a href="{{ route('alumno.calendario') }}" class="btn btn-outline-primary rounded-3 text-start d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar3 me-2"></i>Ver Calendario Mensual</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="{{ route('alumno.justificantes') }}" class="btn btn-outline-secondary rounded-3 text-start d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-upload me-2"></i>Carga Digital de Justificante
                                @if ($incidenciasPorJustificar->count() > 0)
                                    <span class="badge bg-danger ms-2">{{ $incidenciasPorJustificar->count() }} pendiente(s)</span>
                                @endif
                            </span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Tabla de Mis Materias Inscritas, Regla Universitaria (3 Retardos = 1 Falta) y Derecho a Examen -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-journal-check me-2 text-primary"></i>Mis Materias Inscritas y Derecho a Examen Ordinario
                    </h5>
                    <p class="text-muted small mb-0">
                        Regla universitaria: <strong>cada 3 retardos acumulados en una misma asignatura equivalen a 1 falta</strong> (Máx. permitido: 3 faltas).
                    </p>
                </div>
                <span class="badge bg-primary rounded-pill">{{ $matriculasAceptadas->count() }} Materias Aceptadas</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Grupo / Carrera</th>
                            <th>Materia</th>
                            <th class="text-center">Registro (A / R / P / F)</th>
                            <th style="min-width: 155px;">% Asistencia</th>
                            <th class="text-center">Faltas Computadas</th>
                            <th class="text-center">Derecho a Examen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matriculasAceptadas as $mat)
                            @php
                                $pctM = $mat->porcentaje_mat;
                                $colorM = is_null($pctM) ? 'secondary' : ($pctM >= 85 ? 'success' : ($pctM >= 70 ? 'warning' : 'danger'));
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                        {{ $mat->grupo->nombre_grupo ?? '-' }} ({{ $mat->grupo->turno ?? '-' }})
                                    </span>
                                    @if (!empty($mat->grupo->carrera) || !empty($mat->grupo->grado))
                                        <div class="small text-muted mt-1">
                                            {{ $mat->grupo->carrera ? $mat->grupo->carrera . ' — ' : '' }}{{ $mat->grupo->grado ?? '' }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($mat->materia)
                                        <span class="badge bg-dark me-1">{{ $mat->materia->codigo_materia }}</span>
                                        <span class="fw-semibold text-dark">{{ $mat->materia->nombre_materia }}</span>
                                    @else
                                        <span class="text-muted">Sin materia</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success me-1">A: {{ $mat->asistencias_mat }}</span>
                                    <span class="badge bg-warning text-dark me-1">R: {{ $mat->retardos_mat }}</span>
                                    <span class="badge bg-info text-dark me-1">P: {{ $mat->permisos_mat }}</span>
                                    <span class="badge bg-danger">F: {{ $mat->faltas_mat }}</span>
                                </td>
                                <td>
                                    @if (is_null($pctM))
                                        <span class="text-muted small">Sin sesiones aún</span>
                                    @else
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-bold text-{{ $colorM === 'warning' ? 'dark' : $colorM }}">{{ $pctM }}%</span>
                                            <span class="text-muted">{{ $mat->total_sesiones }} sesiones</span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 7px;">
                                            <div class="progress-bar bg-{{ $colorM }}" role="progressbar" data-width="{{ $pctM }}"></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold {{ $mat->faltas_totales_mat >= 3 ? 'text-danger' : ($mat->faltas_totales_mat === 2 ? 'text-warning text-dark' : 'text-success') }}">
                                        {{ $mat->faltas_totales_mat }} / 3 faltas
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $mat->faltas_mat }} dir. + {{ $mat->faltas_retardos_mat }} por {{ $mat->retardos_mat }} ret.
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($mat->faltas_totales_mat >= 3)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-octagon-fill me-1"></i>Sin Derecho a Examen (SD)
                                        </span>
                                    @elseif ($mat->faltas_totales_mat === 2)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>En Riesgo / Preventivo (2/3)
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Con Derecho a Ordinario
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Aún no tienes materias aceptadas. Haz clic en <strong>"Buscar y Solicitar Grupo"</strong> arriba para inscribirte.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Librería para dibujar el QR en pantalla -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        let segundosRestantes = 60;

        function generarNuevoQR() {
            const contenedor = document.getElementById('qrcode');
            const qrUrl = contenedor ? contenedor.getAttribute('data-qr-url') : '/alumno/qr/generar';
            const alertaSesion = document.getElementById('qr-error-sesion');
            const tokenRespaldoEl = document.getElementById('qr-token-respaldo');

            fetch(qrUrl, {
                headers: { "Accept": "application/json" }
            })
                .then(response => {
                    if (response.redirected || !response.ok) {
                        throw new Error('SESION_CAMBIADA');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        if (alertaSesion) alertaSesion.classList.add('d-none');
                        contenedor.innerHTML = ''; // Limpiar QR anterior
                        new QRCode(contenedor, {
                            text: data.qr_token,
                            width: 210,
                            height: 210,
                            colorDark : "#000000",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.L
                        });
                        if (tokenRespaldoEl) {
                            tokenRespaldoEl.textContent = data.qr_token;
                        }
                        segundosRestantes = 60;
                    }
                })
                .catch(() => {
                    if (alertaSesion) alertaSesion.classList.remove('d-none');
                });
        }

        // Contador regresivo de 60 segundos
        setInterval(() => {
            segundosRestantes--;
            if (segundosRestantes <= 0) {
                generarNuevoQR();
            }
            document.getElementById('qr-timer').innerText = `Expira en ${segundosRestantes}s`;
        }, 1000);

        // Confirmación en Tiempo Real cuando el QR del alumno es escaneado por el docente
        function verificarConfirmacionEscaneo() {
            const box = document.getElementById('qr-confirmacion-escaneo');
            const detalle = document.getElementById('qr-confirmacion-detalle');
            if (!box || !detalle) return;

            const estadoUrl = box.getAttribute('data-estado-url');
            if (!estadoUrl) return;

            fetch(estadoUrl, { headers: { "Accept": "application/json" } })
                .then(function (r) {
                    if (!r.ok || r.redirected) return null;
                    return r.json();
                })
                .then(function (data) {
                    if (data && data.escaneado) {
                        const etiquetaEstado = (data.estado || 'asistencia').toUpperCase();
                        const textoGrupo = data.grupo ? ' (Grupo ' + data.grupo + ')' : '';
                        detalle.textContent = data.materia + textoGrupo + ' — ' + etiquetaEstado + ' a las ' + data.hora;
                        box.classList.remove('d-none');
                    }
                })
                .catch(function () {});
        }

        // Generar QR, iniciar polling de confirmación en tiempo real e inicializar barras de progreso
        document.addEventListener('DOMContentLoaded', function () {
            generarNuevoQR();
            verificarConfirmacionEscaneo();
            setInterval(verificarConfirmacionEscaneo, 4000);
            document.querySelectorAll('[data-width]').forEach(function (el) {
                el.style.width = el.getAttribute('data-width') + '%';
            });
        });
    </script>
</body>
</html>
