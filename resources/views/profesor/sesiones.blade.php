<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sesiones de Clase y Escáner QR - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
    <style>
        .acordeon-sae .accordion-item,
        .acordeon-sae-interno .accordion-item {
            border: 1px solid #E2E8F0;
            border-radius: 1rem;
            margin-bottom: .75rem;
            background: #FFFFFF;
            box-shadow: 0 2px 6px -2px rgba(15, 23, 42, .06);
            overflow: hidden;
        }
        .acordeon-sae .accordion-button,
        .acordeon-sae-interno .accordion-button {
            background: #FFFFFF;
            color: #0F172A;
            font-size: .92rem;
            padding: .8rem 1.1rem;
            box-shadow: none;
            gap: .5rem;
        }
        .acordeon-sae .accordion-button:not(.collapsed),
        .acordeon-sae-interno .accordion-button:not(.collapsed) {
            background: #F8FAFC;
            color: #0F172A;
            box-shadow: none;
        }
        .acordeon-sae .accordion-button:focus,
        .acordeon-sae-interno .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
            z-index: 0;
        }
        .acordeon-sae .accordion-body,
        .acordeon-sae-interno .accordion-body {
            padding: 1rem;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('profesor.dashboard') }}">SAE QR | Sesiones y Escáner</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ url('/profesor/dashboard') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
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
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(!$sesionActiva)
            <!-- Formulario para Abrir Nueva Sesión de Clase -->
            <div class="card rounded-4 p-4 bg-white mb-4">
                <h4 class="fw-bold text-dark mb-3">
                    Abrir Nueva Sesión de Clase
                    <span class="info-icon" tabindex="0" role="button" aria-label="Elige materia, grupo y tolerancia para habilitar la lectura de QR.">
                        <i class="bi bi-info-circle"></i>
                        <span class="info-icon-text" role="tooltip">Elige materia, grupo y tolerancia para habilitar la lectura de QR.</span>
                    </span>
                </h4>

                <form action="{{ route('sesiones.iniciar') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Materia y Grupo a Impartir <span class="text-danger">*</span></label>
                        <select name="materia_id" class="form-select rounded-3" required>
                            <option value="" disabled {{ empty($materiaSeleccionada) ? 'selected' : '' }}>Selecciona una asignatura</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}" {{ (isset($materiaSeleccionada) && (int) $materiaSeleccionada === (int) $materia->id) ? 'selected' : '' }}>
                                    [{{ $materia->codigo_materia }}] {{ $materia->nombre_materia }}{{ $materia->grupo ? ' · Grupo: ' . $materia->grupo->nombre_grupo . ' (' . $materia->grupo->turno . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tolerancia (Minutos) <span class="text-danger">*</span></label>
                        <input type="number" name="tolerancia_minutos" class="form-control rounded-3" value="15" min="0" max="60" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-institucional w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-play-circle-fill me-2"></i>Iniciar Clase
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Panel de Sesión Activa y Cámara Escáner QR -->
            <div class="row g-4 mb-5">
                <div class="col-md-5">
                    <div class="card rounded-4 p-4 bg-white text-center h-100">
                        <span class="badge badge-suave-verde mb-2 align-self-center px-3 py-2 rounded-pill">
                            <i class="bi bi-broadcast me-1"></i>SESIÓN ACTIVA EN CURSO
                        </span>
                        <h4 class="fw-bold text-dark mb-1">{{ $sesionActiva->materia->nombre_materia }}</h4>
                        @if($sesionActiva->materia->grupo)
                            <div class="mb-1">
                                <span class="badge rounded-pill px-3 py-1" style="{{ \App\Support\CarreraColor::suave($sesionActiva->materia->grupo->carrera ?? '') }}">
                                    Grupo: {{ $sesionActiva->materia->grupo->nombre_grupo }} ({{ $sesionActiva->materia->grupo->turno }})
                                </span>
                            </div>
                        @endif
                        <p class="texto-slate small mb-2">Inicio: {{ $sesionActiva->hora_inicio }} | Tolerancia: {{ $sesionActiva->tolerancia_minutos }} min</p>

                        <!-- Visor de Cámara -->
                        <div id="reader" class="border rounded-4 overflow-hidden mx-auto my-2 bg-dark" style="width: 100%; max-width: 360px; min-height: 240px;"></div>

                        <div id="alerta-escaneo" class="alert d-none rounded-3 small py-2 mt-2 mb-2" role="alert"></div>

                        <div class="d-flex gap-2 justify-content-center mt-2">
                            <button id="btn-camara" type="button" onclick="iniciarCamara()" class="btn btn-institucional rounded-3 px-3">
                                <i class="bi bi-camera-fill me-1"></i>Encender Cámara
                            </button>
                            <form action="{{ route('sesiones.finalizar', $sesionActiva->id) }}" method="POST" id="formCerrarSesion">
                                @csrf
                                <button type="submit" class="btn btn-danger rounded-3 px-3">
                                    <i class="bi bi-stop-circle-fill me-1"></i>Cerrar Clase
                                </button>
                            </form>
                        </div>

                        <!-- Opción de respaldo para validar código QR manualmente (ideal para pruebas en la misma PC) -->
                        <div class="mt-4 pt-3 border-top text-start">
                            <label for="inputCodigoRespaldo" class="form-label small fw-semibold texto-slate mb-1">
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
                    <div class="card rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Alumnos Registrados en esta Sesión</h5>
                            <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar Tabla
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-moderna table-hover align-middle">
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
                                        <tr>
                                            <td class="fw-semibold">{{ $asis->alumno->name }}</td>
                                            <td class="texto-slate small">{{ $asis->timestamp_escaneo ?? 'Sin escaneo' }}</td>
                                            <td>
                                                @if($asis->estado_incidencia === 'asistencia')
                                                    <span class="badge badge-estado badge-asistencia">Asistencia</span>
                                                @elseif($asis->estado_incidencia === 'retardo')
                                                    <span class="badge badge-estado badge-retardo">Retardo</span>
                                                @elseif($asis->estado_incidencia === 'permiso')
                                                    <span class="badge badge-estado badge-permiso">Permiso</span>
                                                @else
                                                    <span class="badge badge-estado badge-falta">Falta</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('asistencias.manual', $asis->id) }}" method="POST" class="d-inline-flex gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="estado_incidencia" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="asistencia" {{ $asis->estado_incidencia == 'asistencia' ? 'selected' : '' }}>Asistencia</option>
                                                        <option value="retardo" {{ $asis->estado_incidencia == 'retardo' ? 'selected' : '' }}>Retardo</option>
                                                        <option value="permiso" {{ $asis->estado_incidencia == 'permiso' ? 'selected' : '' }}>Permiso</option>
                                                        <option value="falta" {{ $asis->estado_incidencia == 'falta' ? 'selected' : '' }}>Falta</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="fila-vacia-asistencias">
                                            <td colspan="4" class="text-center texto-slate py-4">Aún no se ha escaneado ningún código QR en esta sesión.</td>
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
                 class="d-none">
            </div>

            <!-- Script del Escáner HTML5-QRCode conectado al Backend -->
            <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
            <script>
                let html5QrCode = null;
                let escaneando = false;
                let procesandoLectura = false;

                document.addEventListener('DOMContentLoaded', function () {
                    const formCerrar = document.getElementById('formCerrarSesion');
                    if (formCerrar) {
                        formCerrar.addEventListener('submit', function (e) {
                            if (!confirm('¿Cerrar sesión de clase? Se asignará falta a los alumnos no escaneados.')) {
                                e.preventDefault();
                            }
                        });
                    }
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

                    const tdNombre = document.createElement('td');
                    tdNombre.className = 'fw-semibold';
                    tdNombre.textContent = data.alumno || 'Alumno';

                    const tdHora = document.createElement('td');
                    tdHora.className = 'texto-slate small';
                    tdHora.textContent = data.hora || '';

                    const tdEstado = document.createElement('td');
                    const badge = document.createElement('span');
                    if (data.estado === 'retardo') {
                        badge.className = 'badge badge-estado badge-retardo';
                        badge.textContent = 'Retardo';
                    } else {
                        badge.className = 'badge badge-estado badge-asistencia';
                        badge.textContent = 'Asistencia';
                    }
                    tdEstado.appendChild(badge);

                    const tdManual = document.createElement('td');
                    tdManual.className = 'text-center small texto-slate';
                    tdManual.textContent = 'Recién registrado';

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
                            agregarFilaTablaEnVivo(data);
                        } else if (data.status === 'warning') {
                            mostrarAlertaEscaneo('warning', data.message);
                        } else {
                            mostrarAlertaEscaneo('danger', data.message || 'No se pudo registrar el código QR.');
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
        <div class="card rounded-4 p-4 bg-white mt-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        Historial de Sesiones de Clase
                        <span class="info-icon" tabindex="0" role="button" aria-label="Historial de tus sesiones, asistencias por clase y ajustes manuales.">
                            <i class="bi bi-info-circle"></i>
                            <span class="info-icon-text" role="tooltip">Historial de tus sesiones, asistencias por clase y ajustes manuales.</span>
                        </span>
                    </h5>
                </div>
                <span class="badge badge-estado badge-permiso rounded-pill px-3 py-2">{{ $historialSesiones->count() }} Sesiones Registradas</span>
            </div>

            @php
                $sesionesPorCarrera = $historialSesiones->groupBy(function ($sesHist) {
                    $grupoHist = $sesHist->grupo ?? optional($sesHist->materia)->grupo;
                    return ($grupoHist && $grupoHist->carrera) ? $grupoHist->carrera : 'Carrera general';
                });
            @endphp
            <div class="accordion acordeon-sae" id="accCarreras">
                @forelse($sesionesPorCarrera as $nombreCarrera => $sesionesCarrera)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#accCarrera-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                <i class="bi bi-mortarboard-fill me-2 text-secondary"></i>
                                <span class="fw-bold">{{ $nombreCarrera }}</span>
                                <span class="badge badge-estado neutra-slate ms-2">{{ $sesionesCarrera->count() }} sesiones</span>
                            </button>
                        </h2>
                        <div id="accCarrera-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#accCarreras">
                            <div class="accordion-body">
                                @php
                                    $sesionesPorMateria = $sesionesCarrera->groupBy(function ($sesHist) {
                                        $grupoHist = $sesHist->grupo ?? optional($sesHist->materia)->grupo;
                                        $claveMateria = $sesHist->materia
                                            ? $sesHist->materia->codigo_materia . ' · ' . $sesHist->materia->nombre_materia
                                            : 'Asignatura eliminada';
                                        return $claveMateria . ' — Grupo ' . ($grupoHist ? $grupoHist->nombre_grupo : '-');
                                    });
                                @endphp
                                <div class="accordion acordeon-sae-interno" id="accMaterias-{{ $loop->index }}">
                                    @foreach($sesionesPorMateria as $claveMateria => $sesionesMateria)
                                        @php
                                            $totalA = $sesionesMateria->sum(fn ($s) => $s->asistencias->where('estado_incidencia', 'asistencia')->count());
                                            $totalR = $sesionesMateria->sum(fn ($s) => $s->asistencias->where('estado_incidencia', 'retardo')->count());
                                            $totalP = $sesionesMateria->sum(fn ($s) => $s->asistencias->where('estado_incidencia', 'permiso')->count());
                                            $totalF = $sesionesMateria->sum(fn ($s) => $s->asistencias->where('estado_incidencia', 'falta')->count());
                                        @endphp
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#accMat-{{ $loop->parent->index }}-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                                    <i class="bi bi-journal-bookmark-fill me-2 text-secondary"></i>
                                                    <span class="fw-semibold">{{ $claveMateria }}</span>
                                                    <span class="badge badge-estado neutra-slate ms-2">{{ $sesionesMateria->count() }} sesiones</span>
                                                    <div class="incidencias-box ms-auto d-none d-lg-flex py-1">
                                                        <span class="inc-item inc-a {{ $totalA > 0 ? 'inc-on' : '' }}" title="Asistencias"><span class="dot"></span>A: <strong class="val">{{ $totalA }}</strong></span>
                                                        <span class="inc-item inc-r {{ $totalR > 0 ? 'inc-on' : '' }}" title="Retardos"><span class="dot"></span>R: <strong class="val">{{ $totalR }}</strong></span>
                                                        <span class="inc-item inc-p {{ $totalP > 0 ? 'inc-on' : '' }}" title="Permisos"><span class="dot"></span>P: <strong class="val">{{ $totalP }}</strong></span>
                                                        <span class="inc-item inc-f {{ $totalF > 0 ? 'inc-on' : '' }}" title="Faltas"><span class="dot"></span>F: <strong class="val">{{ $totalF }}</strong></span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="accMat-{{ $loop->parent->index }}-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#accMaterias-{{ $loop->parent->index }}">
                                                <div class="accordion-body p-2">
                                                    <div class="table-responsive">
                                                        <table class="table table-moderna align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Fecha / Horario</th>
                                                                    <th class="text-center">Resumen (A / R / P / F)</th>
                                                                    <th class="text-center">Estado</th>
                                                                    <th class="text-end">Detalle / Ajuste</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($sesionesMateria as $sesHist)
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
                                                                            <div class="texto-slate small">{{ $sesHist->hora_inicio }} — {{ $sesHist->hora_fin ?? 'En curso' }} (Tol: {{ $sesHist->tolerancia_minutos }}m)</div>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="incidencias-box mx-auto">
                                                                                <span class="inc-item inc-a {{ $asistH > 0 ? 'inc-on' : '' }}" title="Asistencias"><span class="dot"></span>A: <strong class="val">{{ $asistH }}</strong></span>
                                                                                <span class="inc-item inc-r {{ $retH > 0 ? 'inc-on' : '' }}" title="Retardos"><span class="dot"></span>R: <strong class="val">{{ $retH }}</strong></span>
                                                                                <span class="inc-item inc-p {{ $permH > 0 ? 'inc-on' : '' }}" title="Permisos"><span class="dot"></span>P: <strong class="val">{{ $permH }}</strong></span>
                                                                                <span class="inc-item inc-f {{ $faltH > 0 ? 'inc-on' : '' }}" title="Faltas"><span class="dot"></span>F: <strong class="val">{{ $faltH }}</strong></span>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($sesHist->estado === 'activa')
                                                                                <span class="badge badge-suave-verde"><i class="bi bi-broadcast me-1"></i>Activa</span>
                                                                            @else
                                                                                <span class="badge badge-estado neutra-slate">Finalizada</span>
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
                                                                                        <div class="modal-header franja-institucional text-white border-0">
                                                                                            <div>
                                                                                                <h5 class="modal-title fw-bold mb-0">
                                                                                                    <i class="bi bi-card-checklist me-2"></i>Pase de Lista — {{ $sesHist->materia->nombre_materia ?? 'Sesión' }}
                                                                                                </h5>
                                                                                                <small class="opacity-75">
                                                                                                    Fecha: {{ $sesHist->fecha }} | Inicio: {{ $sesHist->hora_inicio }} | Grupo: {{ $grupoHist->nombre_grupo ?? '-' }}
                                                                                                </small>
                                                                                            </div>
                                                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                                                        </div>
                                                                                        <div class="modal-body p-4">
                                                                                            <div class="table-responsive">
                                                                                                <table class="table table-sm table-moderna table-hover align-middle mb-0">
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
                                                                                                                    <div class="texto-slate small">{{ $regHist->alumno->matricula_codigo ?: ($regHist->alumno->email ?? '') }}</div>
                                                                                                                </td>
                                                                                                                <td class="texto-slate small">{{ $regHist->timestamp_escaneo ?? 'Sin escaneo QR' }}</td>
                                                                                                                <td>
                                                                                                                    @if($regHist->estado_incidencia === 'asistencia')
                                                                                                                        <span class="badge badge-estado badge-asistencia">Asistencia</span>
                                                                                                                    @elseif($regHist->estado_incidencia === 'retardo')
                                                                                                                        <span class="badge badge-estado badge-retardo">Retardo</span>
                                                                                                                    @elseif($regHist->estado_incidencia === 'permiso')
                                                                                                                        <span class="badge badge-estado badge-permiso">Permiso</span>
                                                                                                                    @else
                                                                                                                        <span class="badge badge-estado badge-falta">Falta</span>
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
                                                                                                                <td colspan="4" class="text-center texto-slate py-3">
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
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="accordion-item">
                        <div class="accordion-body text-center texto-slate py-4">
                            Aún no has impartido sesiones de clase registradas en el historial.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
