<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sesiones de Clase y Escáner QR - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    @include('partials.ui_helpers')

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}"><i class="bi bi-camera-video me-2"></i>SAE QR | Control de Sesiones y Escáner</a>
            <a href="{{ url('/profesor/dashboard') }}" class="btn btn-light text-dark fw-semibold btn-sm rounded-3">
                <i class="bi bi-arrow-left me-1"></i>Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container my-5">
        @if(!$sesionActiva)
            <!-- Formulario para Abrir Nueva Sesión de Clase -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Abrir Nueva Sesión de Clase</h4>
                    <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Selecciona la materia, el tiempo de tolerancia y el tiempo de cierre automático para habilitar la lectura de códigos QR.">!</span>
                </div>

                <form action="{{ route('sesiones.iniciar') }}" method="POST" class="row g-3 align-items-end" id="formIniciarSesion">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Materia y Grupo a Impartir <span class="text-danger">*</span></label>
                        <select name="materia_id" class="form-select rounded-3 js-sae-select" data-search-placeholder="Buscar asignatura o grupo..." required>
                            <option value="" disabled {{ empty($materiaSeleccionada) ? 'selected' : '' }}>Selecciona una asignatura</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}"
                                        data-title="{{ $materia->nombre_materia }}"
                                        data-badge="{{ $materia->codigo_materia }}"
                                        data-sub="{{ $materia->grupo ? 'Grupo: ' . $materia->grupo->nombre_grupo . ' (' . $materia->grupo->turno . ')' : '' }}"
                                        {{ (isset($materiaSeleccionada) && (int) $materiaSeleccionada === (int) $materia->id) ? 'selected' : '' }}>
                                    [{{ $materia->codigo_materia }}] {{ $materia->nombre_materia }}{{ $materia->grupo ? ' — Grupo: ' . $materia->grupo->nombre_grupo . ' (' . $materia->grupo->turno . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tolerancia (Min.) <span class="text-danger">*</span></label>
                        <input type="number" name="tolerancia_minutos" id="inputToleranciaMin" class="form-control rounded-3" value="{{ old('tolerancia_minutos', 15) }}" min="0" max="60" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cierre Automático (Min.) <span class="text-danger">*</span></label>
                        <input type="number" name="duracion_minutos" id="inputDuracionMin" class="form-control rounded-3" value="{{ old('duracion_minutos', 60) }}" min="1" max="300" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-play-circle-fill me-2"></i>Iniciar Clase
                        </button>
                    </div>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const formIniciar = document.getElementById('formIniciarSesion');
                        const inTol = document.getElementById('inputToleranciaMin');
                        const inDur = document.getElementById('inputDuracionMin');
                        if (formIniciar && inTol && inDur) {
                            formIniciar.addEventListener('submit', function (e) {
                                const tol = parseInt(inTol.value, 10);
                                const dur = parseInt(inDur.value, 10);
                                if (!isNaN(tol) && !isNaN(dur) && dur <= tol) {
                                    e.preventDefault();
                                    if (typeof window.mostrarNotificacionSae === 'function') {
                                        window.mostrarNotificacionSae('danger', 'El tiempo de cierre automático (' + dur + ' min) debe ser mayor al tiempo de tolerancia (' + tol + ' min).', 'Verifica los tiempos');
                                    } else {
                                        alert('El tiempo de cierre automático debe ser mayor al tiempo de tolerancia.');
                                    }
                                    inDur.focus();
                                }
                            });
                        }
                    });
                </script>
            </div>
        @else
            @php
                $finCarbonSesion = \App\Http\Controllers\SesionClaseController::obtenerTimestampFin($sesionActiva);
                $segundosRestantesSesion = max(0, (int) \Carbon\Carbon::now()->diffInSeconds($finCarbonSesion, false));
            @endphp
            <!-- Panel de Sesión Activa y Cámara Escáner QR -->
            <div class="row g-4 mb-5">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                        <span class="badge bg-success mb-2 align-self-center px-3 py-2 rounded-pill">
                            <i class="bi bi-broadcast me-1"></i>SESIÓN ACTIVA EN CURSO
                        </span>
                        <h4 class="fw-bold text-dark mb-1">{{ $sesionActiva->materia->nombre_materia }}</h4>
                        @if($sesionActiva->materia->grupo)
                            <div class="mb-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-1">
                                    Grupo: {{ $sesionActiva->materia->grupo->nombre_grupo }} ({{ $sesionActiva->materia->grupo->turno }})
                                </span>
                            </div>
                        @endif
                        <p class="text-muted small mb-2">
                            Inicio: {{ $sesionActiva->hora_inicio }} | Cierre progr.: {{ $sesionActiva->hora_fin ?? '--:--' }} | Tolerancia: {{ $sesionActiva->tolerancia_minutos }} min
                        </p>

                        <!-- Reloj Regresivo de Cierre Automático -->
                        <div class="mb-2">
                            <span id="badge-tiempo-restante" class="badge bg-dark px-3 py-2 rounded-pill fs-6 font-monospace">
                                <i class="bi bi-hourglass-split me-1"></i>Tiempo restante: <span id="texto-tiempo-restante">--:--</span>
                            </span>
                        </div>

                        <!-- Aviso de Últimos 15 Segundos antes del Cierre Automático Estricto -->
                        <div id="alerta-cierre-15s" class="alert alert-danger border-danger border-2 rounded-3 py-2 px-3 mb-2 d-none fw-semibold small shadow-sm" role="alert">
                            <i class="bi bi-alarm-fill me-1"></i>
                            ¡Atención! La sesión terminará y la cámara se cerrará automáticamente en <span id="contador-15s" class="fs-6 fw-bold">15</span> segundos.
                        </div>

                        <!-- Visor de Cámara -->
                        <div id="reader" class="border rounded-4 overflow-hidden mx-auto my-2 bg-dark" style="width: 100%; max-width: 360px; min-height: 240px;"></div>

                        <div id="alerta-escaneo" class="alert d-none rounded-3 small py-2 mt-2 mb-2" role="alert"></div>

                        <!-- Tarjeta de Verificación Visual Anti-Suplantación del Último Alumno Escaneado -->
                        <div id="tarjeta-verificacion-alumno" class="d-none border rounded-4 p-3 my-2 bg-light text-start shadow-sm">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <img id="verif-foto" src="" alt="Foto Alumno" class="rounded-circle object-fit-cover border shadow-sm d-none" style="width: 54px; height: 54px;">
                                    <div id="verif-inicial" class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 text-white shadow-sm" style="width: 54px; height: 54px; background-color: var(--pantone-294c);">A</div>
                                    <div>
                                        <div class="fw-bold text-dark" id="verif-nombre">Nombre del Alumno</div>
                                        <div class="small text-muted font-monospace" id="verif-matricula">Matrícula: ---</div>
                                        <span id="verif-badge" class="badge bg-success mt-1">ASISTENCIA</span>
                                    </div>
                                </div>
                                <button type="button" id="btn-denegar-ultimo" class="btn btn-danger btn-sm rounded-3 fw-semibold">
                                    <i class="bi bi-x-octagon-fill me-1"></i>Denegar (Falta)
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-center mt-2">
                            <button id="btn-camara" type="button" onclick="iniciarCamara()" class="btn btn-primary rounded-3 px-3">
                                <i class="bi bi-camera-fill me-1"></i>Encender Cámara
                            </button>
                            <form action="{{ route('sesiones.finalizar', $sesionActiva->id) }}" method="POST" id="formCerrarSesion">
                                @csrf
                                <input type="hidden" name="automatico" id="inputCierreAutomatico" value="0">
                                <button type="submit" class="btn btn-danger rounded-3 px-3">
                                    <i class="bi bi-stop-circle-fill me-1"></i>Cerrar Clase
                                </button>
                            </form>
                        </div>

                        <!-- Opción de respaldo para validar código QR manualmente (ideal para pruebas en la misma PC) -->
                        <div class="mt-4 pt-3 border-top text-start">
                            <label for="inputCodigoRespaldo" class="form-label small fw-semibold text-muted mb-1">
                                <i class="bi bi-keyboard me-1"></i>¿Probando en una sola PC o sin cámara? Pega aquí el código de respaldo del alumno:
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="inputCodigoRespaldo" class="form-control font-monospace" placeholder="Ej. 5.1761509200.a8f39c1b4d2e07f1">
                                <button type="button" class="btn btn-outline-primary fw-semibold" onclick="validarTokenManual()">
                                    Validar Código
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Asistencia en Tiempo Real -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Alumnos Registrados en esta Sesión</h5>
                            <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar Tabla
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Hora Escaneo</th>
                                        <th>Estado</th>
                                        <th class="text-center">Ajuste Manual</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-asistencias-body">
                                    @forelse($sesionActiva->asistencias as $asis)
                                        <tr id="fila-registro-{{ $asis->id }}">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if(optional($asis->alumno)->foto_perfil)
                                                        <img src="{{ asset('storage/' . $asis->alumno->foto_perfil) }}" alt="Foto" class="rounded-circle object-fit-cover border" style="width: 34px; height: 34px;">
                                                    @else
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold small text-white" style="width: 34px; height: 34px; background-color: var(--pantone-294c);">
                                                            {{ mb_strtoupper(mb_substr(optional($asis->alumno)->name ?? 'A', 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $asis->alumno->name ?? 'Alumno' }}</div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $asis->alumno->matricula_codigo ?? 'Sin matrícula' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted small">{{ $asis->timestamp_escaneo ?? 'Sin escaneo' }}</td>
                                            <td class="js-celda-estado">
                                                @if($asis->estado_incidencia === 'asistencia')
                                                    <span class="badge bg-success">Asistencia</span>
                                                @elseif($asis->estado_incidencia === 'retardo')
                                                    <span class="badge bg-warning text-dark">Retardo</span>
                                                @elseif($asis->estado_incidencia === 'permiso')
                                                    <span class="badge bg-primary">Permiso</span>
                                                @else
                                                    <span class="badge bg-danger">Falta</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <select
                                                    name="estado_incidencia"
                                                    class="form-select form-select-sm js-select-estado-vivo"
                                                    data-url="{{ route('asistencias.manual', $asis->id) }}"
                                                    data-registro-id="{{ $asis->id }}"
                                                >
                                                    <option value="asistencia" {{ $asis->estado_incidencia == 'asistencia' ? 'selected' : '' }}>Asistencia</option>
                                                    <option value="retardo" {{ $asis->estado_incidencia == 'retardo' ? 'selected' : '' }}>Retardo</option>
                                                    <option value="permiso" {{ $asis->estado_incidencia == 'permiso' ? 'selected' : '' }}>Permiso</option>
                                                    <option value="falta" {{ $asis->estado_incidencia == 'falta' ? 'selected' : '' }}>Falta</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="fila-vacia-asistencias">
                                            <td colspan="4" class="text-center text-muted py-4">Aún no se ha escaneado ningún código QR en esta sesión.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guardamos los datos de Blade en atributos HTML para que JS no marque error en VS Code -->
            <div id="datos-sesion" 
                 data-url="{{ route('qr.validar') }}" 
                 data-sesion-id="{{ $sesionActiva->id }}"
                 data-segundos-restantes="{{ $segundosRestantesSesion }}"
                 class="d-none">
            </div>

            <!-- Script del Escáner HTML5-QRCode conectado al Backend -->
            <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
            <script>
                let html5QrCode = null;
                let escaneando = false;
                let procesandoLectura = false;
                let ultimoRegistroEscaneado = null;
                let cierreAutomaticoEnCurso = false;
                let aviso15sMostrado = false;
                let timerCierreSesion = null;

                function formatearMinutosSegundos(totalSeg) {
                    const segs = Math.max(0, totalSeg);
                    const hrs = Math.floor(segs / 3600);
                    const mins = Math.floor((segs % 3600) / 60);
                    const s = segs % 60;
                    const mm = String(mins).padStart(2, '0');
                    const ss = String(s).padStart(2, '0');
                    return hrs > 0 ? (String(hrs).padStart(2, '0') + ':' + mm + ':' + ss) : (mm + ':' + ss);
                }

                function detenerCamaraYCerrarAutomaticamente() {
                    if (cierreAutomaticoEnCurso) return;
                    cierreAutomaticoEnCurso = true;
                    procesandoLectura = true;

                    if (timerCierreSesion) {
                        clearInterval(timerCierreSesion);
                    }

                    const btnCam = document.getElementById('btn-camara');
                    if (btnCam) btnCam.disabled = true;

                    const inputAuto = document.getElementById('inputCierreAutomatico');
                    if (inputAuto) inputAuto.value = '1';

                    const enviarCierre = function () {
                        const formCerrar = document.getElementById('formCerrarSesion');
                        if (formCerrar) {
                            formCerrar.submit();
                        } else {
                            location.reload();
                        }
                    };

                    if (html5QrCode && escaneando) {
                        escaneando = false;
                        html5QrCode.stop()
                            .then(function () { enviarCierre(); })
                            .catch(function () { enviarCierre(); });
                    } else {
                        enviarCierre();
                    }
                }

                function iniciarTemporizadorCierreAutomatico() {
                    const datosEl = document.getElementById('datos-sesion');
                    if (!datosEl) return;

                    let restantes = parseInt(datosEl.getAttribute('data-segundos-restantes') || '0', 10);
                    const textoRestante = document.getElementById('texto-tiempo-restante');
                    const badgeRestante = document.getElementById('badge-tiempo-restante');
                    const alerta15s = document.getElementById('alerta-cierre-15s');
                    const contador15s = document.getElementById('contador-15s');

                    function tick() {
                        if (textoRestante) {
                            textoRestante.textContent = formatearMinutosSegundos(restantes);
                        }

                        if (restantes <= 15 && restantes > 0) {
                            if (badgeRestante) {
                                badgeRestante.classList.remove('bg-dark');
                                badgeRestante.classList.add('bg-danger');
                            }
                            if (alerta15s) alerta15s.classList.remove('d-none');
                            if (contador15s) contador15s.textContent = restantes;

                            if (!aviso15sMostrado) {
                                aviso15sMostrado = true;
                                if (typeof window.mostrarNotificacionSae === 'function') {
                                    window.mostrarNotificacionSae('danger', 'La sesión de clase y la cámara se cerrarán automáticamente en 15 segundos.', '¡Cierre inminente!');
                                }
                            }
                        }

                        if (restantes <= 0) {
                            if (contador15s) contador15s.textContent = '0';
                            detenerCamaraYCerrarAutomaticamente();
                            return;
                        }

                        restantes--;
                    }

                    tick();
                    if (restantes > 0 && !cierreAutomaticoEnCurso) {
                        timerCierreSesion = setInterval(tick, 1000);
                    }
                }

                function obtenerBadgeEstadoHtml(estado) {
                    if (estado === 'asistencia') return '<span class="badge bg-success">Asistencia</span>';
                    if (estado === 'retardo') return '<span class="badge bg-warning text-dark">Retardo</span>';
                    if (estado === 'permiso') return '<span class="badge bg-primary">Permiso</span>';
                    return '<span class="badge bg-danger">Falta</span>';
                }

                function cambiarEstadoAsistenciaAjax(urlManual, registroId, nuevoEstado) {
                    if (!urlManual) return;
                    fetch(urlManual, {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ estado_incidencia: nuevoEstado })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res && res.status === 'success') {
                            const fila = document.getElementById('fila-registro-' + registroId);
                            if (fila) {
                                const celdaEstado = fila.querySelector('.js-celda-estado');
                                if (celdaEstado) celdaEstado.innerHTML = obtenerBadgeEstadoHtml(nuevoEstado);
                                const selectFila = fila.querySelector('.js-select-estado-vivo');
                                if (selectFila) selectFila.value = nuevoEstado;
                            }
                            if (ultimoRegistroEscaneado && Number(ultimoRegistroEscaneado.registro_id) === Number(registroId)) {
                                const verifBadge = document.getElementById('verif-badge');
                                if (verifBadge) {
                                    verifBadge.className = 'badge mt-1 ' + (nuevoEstado === 'asistencia' ? 'bg-success' : (nuevoEstado === 'retardo' ? 'bg-warning text-dark' : (nuevoEstado === 'permiso' ? 'bg-primary' : 'bg-danger')));
                                    verifBadge.textContent = nuevoEstado.toUpperCase();
                                }
                            }
                            if (typeof window.mostrarNotificacionSae === 'function') {
                                window.mostrarNotificacionSae(nuevoEstado === 'falta' ? 'danger' : 'success', res.message || 'Estado actualizado.', 'Ajuste Manual');
                            }
                        }
                    })
                    .catch(function () {});
                }

                function mostrarTarjetaVerificacion(data) {
                    ultimoRegistroEscaneado = data;
                    const card = document.getElementById('tarjeta-verificacion-alumno');
                    const img = document.getElementById('verif-foto');
                    const inicial = document.getElementById('verif-inicial');
                    const nombre = document.getElementById('verif-nombre');
                    const matricula = document.getElementById('verif-matricula');
                    const badge = document.getElementById('verif-badge');
                    const btnDenegar = document.getElementById('btn-denegar-ultimo');

                    if (!card) return;
                    card.classList.remove('d-none');

                    if (data.foto_url) {
                        img.src = data.foto_url;
                        img.classList.remove('d-none');
                        inicial.classList.add('d-none');
                    } else {
                        img.classList.add('d-none');
                        inicial.textContent = data.inicial || 'A';
                        inicial.classList.remove('d-none');
                    }

                    nombre.textContent = data.alumno || 'Alumno';
                    matricula.textContent = 'Matrícula: ' + (data.matricula || 'Sin matrícula');
                    badge.className = 'badge mt-1 ' + (data.estado === 'retardo' ? 'bg-warning text-dark' : 'bg-success');
                    badge.textContent = (data.estado || 'asistencia').toUpperCase();

                    btnDenegar.onclick = function () {
                        cambiarEstadoAsistenciaAjax(data.url_manual, data.registro_id, 'falta');
                    };
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const formCerrar = document.getElementById('formCerrarSesion');
                    if (formCerrar) {
                        formCerrar.addEventListener('submit', function (e) {
                            if (!cierreAutomaticoEnCurso && !confirm('¿Cerrar sesión de clase? Se asignará falta a los alumnos no escaneados.')) {
                                e.preventDefault();
                            }
                        });
                    }

                    document.querySelectorAll('.js-select-estado-vivo').forEach(function (sel) {
                        sel.addEventListener('change', function () {
                            cambiarEstadoAsistenciaAjax(this.getAttribute('data-url'), this.getAttribute('data-registro-id'), this.value);
                        });
                    });

                    iniciarTemporizadorCierreAutomatico();
                });

                function mostrarAlertaEscaneo(tipo, mensaje) {
                    const alerta = document.getElementById('alerta-escaneo');
                    alerta.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
                    if (tipo === 'success') {
                        alerta.classList.add('alert-success');
                    } else if (tipo === 'warning') {
                        alerta.classList.add('alert-warning');
                    } else {
                        alerta.classList.add('alert-danger');
                    }
                    alerta.innerText = mensaje;
                }

                function agregarFilaTablaEnVivo(data) {
                    const tbody = document.getElementById('tabla-asistencias-body');
                    const filaVacia = document.getElementById('fila-vacia-asistencias');
                    if (filaVacia) {
                        filaVacia.remove();
                    }

                    const tr = document.createElement('tr');
                    tr.className = 'table-success';
                    tr.id = 'fila-registro-' + data.registro_id;

                    const tdNombre = document.createElement('td');
                    const wrapAlumno = document.createElement('div');
                    wrapAlumno.className = 'd-flex align-items-center gap-2';

                    if (data.foto_url) {
                        const imgAvatar = document.createElement('img');
                        imgAvatar.src = data.foto_url;
                        imgAvatar.className = 'rounded-circle object-fit-cover border';
                        imgAvatar.style.width = '34px';
                        imgAvatar.style.height = '34px';
                        wrapAlumno.appendChild(imgAvatar);
                    } else {
                        const divAvatar = document.createElement('div');
                        divAvatar.className = 'rounded-circle d-flex align-items-center justify-content-center fw-bold small text-white';
                        divAvatar.style.width = '34px';
                        divAvatar.style.height = '34px';
                        divAvatar.style.backgroundColor = 'var(--pantone-294c)';
                        divAvatar.textContent = data.inicial || 'A';
                        wrapAlumno.appendChild(divAvatar);
                    }

                    const infoDiv = document.createElement('div');
                    const divNom = document.createElement('div');
                    divNom.className = 'fw-semibold';
                    divNom.textContent = data.alumno || 'Alumno';
                    const divMat = document.createElement('div');
                    divMat.className = 'text-muted';
                    divMat.style.fontSize = '0.75rem';
                    divMat.textContent = data.matricula || 'Sin matrícula';
                    infoDiv.appendChild(divNom);
                    infoDiv.appendChild(divMat);
                    wrapAlumno.appendChild(infoDiv);
                    tdNombre.appendChild(wrapAlumno);

                    const tdHora = document.createElement('td');
                    tdHora.className = 'text-muted small';
                    tdHora.textContent = data.hora || '';

                    const tdEstado = document.createElement('td');
                    tdEstado.className = 'js-celda-estado';
                    tdEstado.innerHTML = obtenerBadgeEstadoHtml(data.estado || 'asistencia');

                    const tdManual = document.createElement('td');
                    tdManual.className = 'text-center';

                    const selectManual = document.createElement('select');
                    selectManual.className = 'form-select form-select-sm js-select-estado-vivo';
                    selectManual.setAttribute('data-url', data.url_manual);
                    selectManual.setAttribute('data-registro-id', data.registro_id);

                    [
                        { val: 'asistencia', text: 'Asistencia' },
                        { val: 'retardo', text: 'Retardo' },
                        { val: 'permiso', text: 'Permiso' },
                        { val: 'falta', text: 'Falta' }
                    ].forEach(function (op) {
                        const optionEl = document.createElement('option');
                        optionEl.value = op.val;
                        optionEl.textContent = op.text;
                        if (op.val === data.estado) optionEl.selected = true;
                        selectManual.appendChild(optionEl);
                    });

                    selectManual.addEventListener('change', function () {
                        cambiarEstadoAsistenciaAjax(data.url_manual, data.registro_id, this.value);
                    });

                    tdManual.appendChild(selectManual);

                    tr.appendChild(tdNombre);
                    tr.appendChild(tdHora);
                    tr.appendChild(tdEstado);
                    tr.appendChild(tdManual);

                    tbody.insertBefore(tr, tbody.firstChild);
                }

                function iniciarCamara() {
                    if (escaneando) return;

                    html5QrCode = new Html5Qrcode("reader", {
                        experimentalFeatures: { useBarCodeDetectorIfSupported: true }
                    });

                    const config = {
                        fps: 15,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    };

                    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
                        .then(function () {
                            escaneando = true;
                            mostrarAlertaEscaneo('success', 'Cámara encendida. Acerca el código QR del alumno al recuadro.');
                        })
                        .catch(function () {
                            // Si falla facingMode: environment en webcam de PC, usar la primera cámara disponible
                            Html5Qrcode.getCameras().then(function (cameras) {
                                if (cameras && cameras.length > 0) {
                                    html5QrCode.start(cameras[0].id, config, onScanSuccess)
                                        .then(function () {
                                            escaneando = true;
                                            mostrarAlertaEscaneo('success', 'Cámara encendida. Acerca el código QR del alumno al recuadro.');
                                        });
                                } else {
                                    mostrarAlertaEscaneo('danger', 'No se detectó ninguna cámara disponible en este dispositivo.');
                                }
                            }).catch(function () {
                                mostrarAlertaEscaneo('danger', 'No se pudo acceder a la cámara. Verifica los permisos del navegador.');
                            });
                        });
                }

                function enviarTokenAlServidor(tokenTexto) {
                    if (procesandoLectura) return;
                    procesandoLectura = true;

                    if (html5QrCode && escaneando) {
                        try { html5QrCode.pause(); } catch (e) {}
                    }

                    const datosEl = document.getElementById('datos-sesion');
                    const urlValidar = datosEl.getAttribute('data-url');
                    const sesionId = datosEl.getAttribute('data-sesion-id');

                    fetch(urlValidar, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            qr_token: tokenTexto,
                            sesion_clase_id: sesionId
                        })
                    })
                    .then(function (res) {
                        if (res.redirected || res.status === 419 || res.status === 401 && !res.headers.get('content-type')?.includes('application/json')) {
                            throw new Error('SESION_PROFESOR_PERDIDA');
                        }
                        return res.json();
                    })
                    .then(function (data) {
                        if (data.status === 'success') {
                            mostrarAlertaEscaneo('success', data.message);
                            mostrarTarjetaVerificacion(data);
                            agregarFilaTablaEnVivo(data);
                        } else if (data.status === 'warning') {
                            mostrarAlertaEscaneo('warning', data.message);
                        } else {
                            mostrarAlertaEscaneo('danger', data.message || 'No se pudo registrar el código QR.');
                            if (data.sesion_cerrada) {
                                detenerCamaraYCerrarAutomaticamente();
                            }
                        }
                    })
                    .catch(function (err) {
                        if (err && err.message === 'SESION_PROFESOR_PERDIDA') {
                            mostrarAlertaEscaneo('danger', '¡Atención! Tu sesión de Profesor cambió porque iniciaste sesión como Alumno en otra pestaña del mismo navegador. Abre el Alumno en una ventana de Incógnito o en tu celular.');
                        } else {
                            mostrarAlertaEscaneo('danger', 'Error de conexión al validar el QR. Intenta nuevamente.');
                        }
                    })
                    .finally(function () {
                        setTimeout(function () {
                            procesandoLectura = false;
                            if (html5QrCode && escaneando) {
                                try { html5QrCode.resume(); } catch (e) {}
                            }
                        }, 2000);
                    });
                }

                function onScanSuccess(decodedText) {
                    enviarTokenAlServidor(decodedText);
                }

                function validarTokenManual() {
                    const input = document.getElementById('inputCodigoRespaldo');
                    const valor = input ? input.value.trim() : '';
                    if (!valor) {
                        mostrarAlertaEscaneo('warning', 'Pega primero el código de respaldo del alumno.');
                        return;
                    }
                    enviarTokenAlServidor(valor);
                    input.value = '';
                }
            </script>
        @endif

        <!-- Historial de Sesiones Anteriores Impartidas por el Docente -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mt-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Historial de Sesiones de Clase
                    </h5>
                    <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Consulta todas tus sesiones impartidas, el resumen de asistencias por clase y realiza ajustes manuales si es necesario.">!</span>
                </div>
                <span class="badge bg-dark rounded-pill px-3 py-2">{{ $historialSesiones->total() }} Sesiones Registradas</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha / Horario</th>
                            <th>Asignatura</th>
                            <th>Grupo / Semestre</th>
                            <th class="text-center">Resumen (A / R / P / F)</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Detalle / Ajuste</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historialSesiones as $sesHist)
                            @php
                                $grupoHist = $sesHist->grupo ?? optional($sesHist->materia)->grupo;
                                $asistH = $sesHist->asistencias->where('estado_incidencia', 'asistencia')->count();
                                $retH   = $sesHist->asistencias->where('estado_incidencia', 'retardo')->count();
                                $permH  = $sesHist->asistencias->where('estado_incidencia', 'permiso')->count();
                                $faltH  = $sesHist->asistencias->where('estado_incidencia', 'falta')->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="bi bi-calendar3 me-1 text-secondary"></i>{{ $sesHist->fecha }}</div>
                                    <div class="text-muted small">{{ $sesHist->hora_inicio }} — {{ $sesHist->hora_fin ?? 'En curso' }} (Tol: {{ $sesHist->tolerancia_minutos }}m)</div>
                                </td>
                                <td>
                                    @if($sesHist->materia)
                                        <span class="badge bg-secondary me-1">{{ $sesHist->materia->codigo_materia }}</span>
                                        <span class="fw-semibold text-dark">{{ $sesHist->materia->nombre_materia }}</span>
                                    @else
                                        <span class="text-muted">Asignatura eliminada</span>
                                    @endif
                                </td>
                                <td>
                                    @if($grupoHist)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            {{ $grupoHist->nombre_grupo }} ({{ $grupoHist->grado }})
                                        </span>
                                        @if($grupoHist->carrera)
                                            <div class="text-muted small mt-1">{{ $grupoHist->carrera }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success me-1" title="Asistencias">A: {{ $asistH }}</span>
                                    <span class="badge bg-warning text-dark me-1" title="Retardos">R: {{ $retH }}</span>
                                    <span class="badge bg-info text-dark me-1" title="Permisos">P: {{ $permH }}</span>
                                    <span class="badge bg-danger" title="Faltas">F: {{ $faltH }}</span>
                                </td>
                                <td class="text-center">
                                    @if($sesHist->estado === 'activa')
                                        <span class="badge bg-success"><i class="bi bi-broadcast me-1"></i>Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Finalizada</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalDetalleSesion-{{ $sesHist->id }}">
                                        <i class="bi bi-list-check me-1"></i>Ver Pase de Lista ({{ $sesHist->asistencias->count() }})
                                    </button>

                                    <!-- Modal con el Pase de Lista de la Sesión Anterior -->
                                    <div class="modal fade text-start" id="modalDetalleSesion-{{ $sesHist->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header bg-dark text-white border-0">
                                                    <div>
                                                        <h5 class="modal-title fw-bold mb-0">
                                                            <i class="bi bi-card-checklist me-2 text-info"></i>Pase de Lista — {{ $sesHist->materia->nombre_materia ?? 'Sesión' }}
                                                        </h5>
                                                        <small class="text-light opacity-75">
                                                            Fecha: {{ $sesHist->fecha }} | Inicio: {{ $sesHist->hora_inicio }} | Grupo: {{ $grupoHist->nombre_grupo ?? '-' }}
                                                        </small>
                                                    </div>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Alumno</th>
                                                                    <th>Hora Escaneo</th>
                                                                    <th>Estado Actual</th>
                                                                    <th class="text-end">Cambiar Estado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($sesHist->asistencias as $regHist)
                                                                    <tr>
                                                                        <td>
                                                                            <div class="fw-semibold">{{ $regHist->alumno->name ?? 'Alumno' }}</div>
                                                                            <div class="text-muted small">{{ $regHist->alumno->matricula_codigo ?? $regHist->alumno->email ?? '' }}</div>
                                                                        </td>
                                                                        <td class="text-muted small">{{ $regHist->timestamp_escaneo ?? 'Sin escaneo QR' }}</td>
                                                                        <td>
                                                                            @if($regHist->estado_incidencia === 'asistencia')
                                                                                <span class="badge bg-success">Asistencia</span>
                                                                            @elseif($regHist->estado_incidencia === 'retardo')
                                                                                <span class="badge bg-warning text-dark">Retardo</span>
                                                                            @elseif($regHist->estado_incidencia === 'permiso')
                                                                                <span class="badge bg-info text-dark">Permiso</span>
                                                                            @else
                                                                                <span class="badge bg-danger">Falta</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <form action="{{ route('asistencias.manual', $regHist->id) }}" method="POST" class="d-inline-flex">
                                                                                @csrf
                                                                                @method('PATCH')
                                                                                <select name="estado_incidencia" class="form-select form-select-sm" onchange="this.form.submit()">
                                                                                    <option value="asistencia" {{ $regHist->estado_incidencia === 'asistencia' ? 'selected' : '' }}>Asistencia</option>
                                                                                    <option value="retardo" {{ $regHist->estado_incidencia === 'retardo' ? 'selected' : '' }}>Retardo</option>
                                                                                    <option value="permiso" {{ $regHist->estado_incidencia === 'permiso' ? 'selected' : '' }}>Permiso</option>
                                                                                    <option value="falta" {{ $regHist->estado_incidencia === 'falta' ? 'selected' : '' }}>Falta</option>
                                                                                </select>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="4" class="text-center text-muted py-3">
                                                                            No hay registros de asistencia en esta sesión.
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aún no has impartido sesiones de clase registradas en el historial.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($historialSesiones->hasPages())
                <div class="pt-3 mt-3 border-top d-flex justify-content-center">
                    {{ $historialSesiones->links() }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
