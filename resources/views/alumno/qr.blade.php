<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Código QR Temporal - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
    <style>
        .tarjeta-qr { max-width: 560px; margin-inline: auto; transition: box-shadow .2s ease, border-color .2s ease; }

        .cintillo-clase { display: inline-flex; align-items: center; gap: .5rem; border-radius: 50rem; padding: .42rem .95rem; font-size: .78rem; font-weight: 600; max-width: 100%; }
        .cintillo-curso { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .cintillo-listo { background: #F1F5F9; color: #334155; border: 1px solid #E2E8F0; }
        .punto-pulso { width: 8px; height: 8px; border-radius: 50%; background: #059669; flex-shrink: 0; animation: pulso 1.6s infinite; }
        @keyframes pulso {
            0%   { box-shadow: 0 0 0 0 rgba(5,150,105,.45); }
            70%  { box-shadow: 0 0 0 7px rgba(5,150,105,0); }
            100% { box-shadow: 0 0 0 0 rgba(5,150,105,0); }
        }

        .avatar-identidad {
            width: 52px; height: 52px; border-radius: 50%;
            background: #EFF6FF; color: #2563EB;
            font-weight: 800; font-size: 1rem;
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid #BFDBFE; flex-shrink: 0;
        }

        .marco-qr {
            background: #FFFFFF;
            border: 1px solid rgba(203, 213, 225, .9);
            border-radius: 1.25rem;
            box-shadow: var(--panel-shadow);
        }

        .contenedor-expira { height: 8px; border-radius: 50rem; background: #E2E8F0; overflow: hidden; max-width: 246px; }
        #barra-expira { height: 100%; width: 100%; border-radius: 50rem; background: #2563EB; transition: width .9s linear, background .3s ease; }
        #qr-timer.expira-amarillo { background: #FFFBEB !important; color: #B45309 !important; border-color: #FCD34D !important; }

        #tarjeta-qr.qr-confirmado { border-color: #A7F3D0; box-shadow: 0 0 0 4px rgba(5,150,105,.10), var(--panel-shadow); }

        #tarjeta-qr.modo-lectura .marco-qr {
            width: min(320px, 88vw);
            height: auto;
            aspect-ratio: 1;
            padding: 1rem;
            border-color: #CBD5E1;
            box-shadow: var(--panel-shadow-lg);
        }
        #tarjeta-qr.modo-lectura #qrcode canvas,
        #tarjeta-qr.modo-lectura #qrcode img { transform: scale(1.32); transform-origin: center; }
        .btn-modo-lectura[aria-pressed="true"] { background: #2563EB !important; color: #FFFFFF !important; border-color: #2563EB !important; }

        .acordeon-pruebas summary { cursor: pointer; color: #64748B; font-size: .78rem; font-weight: 600; list-style: none; display: inline-flex; align-items: center; gap: .4rem; }
        .acordeon-pruebas summary::-webkit-details-marker { display: none; }
        .acordeon-pruebas summary:hover { color: #334155; }
    </style>
</head>
<body>

    @php
        $alumno = \Illuminate\Support\Facades\Auth::user();
        $inicialesAlumno = mb_strtoupper(implode('', array_map(fn ($palabra) => mb_substr($palabra, 0, 1), array_slice(preg_split('/\s+/', trim($alumno->name ?? 'U')), 0, 2))));

        // Contexto de clase activa (solo lectura; si no hay sesion, la tarjeta muestra estado neutro)
        $materiaIdsQr = \App\Models\Matricula::where('user_id', $alumno->id)->where('estado', 'aceptado')->pluck('materia_id')->filter()->values();
        $sesionQrActiva = null;
        if ($materiaIdsQr->isNotEmpty()) {
            $sesionQrActiva = \App\Models\SesionClase::with('materia.grupo')
                ->whereIn('materia_id', $materiaIdsQr)
                ->where('estado', 'activa')
                ->first();
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('alumno.dashboard') }}">SAE QR | Mi Código QR</a>
            <div class="d-flex align-items-center gap-2">
                @if ($alumno->foto_perfil)
                    <img src="{{ asset('storage/' . $alumno->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid #E2E8F0;">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 34px; height: 34px; background: #EFF6FF; color: #2563EB; font-size: .75rem;">{{ $inicialesAlumno }}</span>
                @endif
                <a href="{{ route('alumno.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Inicio
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div id="tarjeta-qr" class="card rounded-4 p-4 bg-white text-center tarjeta-qr">
                    <!-- Cintillo de contexto: clase en curso o listo -->
                    <span class="cintillo-clase mx-auto mb-3 {{ $sesionQrActiva ? 'cintillo-curso' : 'cintillo-listo' }}">
                        @if ($sesionQrActiva)
                            <span class="punto-pulso"></span>
                            <span>En curso · {{ $sesionQrActiva->materia->nombre_materia }}{{ $sesionQrActiva->materia->grupo ? ' · Grupo ' . $sesionQrActiva->materia->grupo->nombre_grupo : '' }}</span>
                        @else
                            <i class="bi bi-qr-code"></i>
                            <span>Listo para registro de asistencia</span>
                        @endif
                    </span>

                    <!-- Identidad del alumno: el docente confirma visualmente al escanear -->
                    <div class="d-flex align-items-center justify-content-center gap-3 text-start mb-3">
                        <span class="avatar-identidad">{{ $inicialesAlumno }}</span>
                        <div>
                            <div class="fw-bold" style="color: #0F172A; font-size: 1.05rem;">{{ $alumno->name }}</div>
                            <div class="small texto-slate">
                                Matrícula: <span class="fw-semibold" style="color: #0F172A;">{{ $alumno->matricula_codigo ?? 'Sin matrícula' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmación en Tiempo Real cuando el QR del Alumno es escaneado -->
                    <div id="qr-confirmacion-escaneo" data-estado-url="{{ route('qr.estado') }}" class="alert alert-success border-success border-2 rounded-4 py-2 px-3 mb-3 text-start" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                            <div class="small">
                                <div class="fw-bold text-success">Asistencia registrada</div>
                                <div id="qr-confirmacion-detalle" class="text-dark"></div>
                            </div>
                        </div>
                    </div>

                    <div class="marco-qr d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: min(246px, 78vw); height: min(246px, 78vw);">
                        <div id="qrcode" data-qr-url="{{ route('qr.generar') }}"></div>
                    </div>

                    <div id="qr-error-sesion" class="alert alert-warning small py-2 px-3 d-none mb-2">
                        Tu sesión de Alumno cambió porque iniciaste sesión como Profesor en otra pestaña del mismo navegador. Abre el perfil de Profesor o Alumno en una <strong>ventana de incógnito</strong> o en otro dispositivo.
                    </div>

                    <!-- Indicador de expiración: píldora + barra (azul -> ámbar en los últimos 10s) -->
                    <div class="mb-2">
                        <span id="qr-timer" class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
                            Actualizando en 60s
                        </span>
                        <div class="contenedor-expira mx-auto mt-2">
                            <div id="barra-expira"></div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-1">
                        <button onclick="generarNuevoQR()" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                            <i class="bi bi-arrow-clockwise me-1"></i>Renovar QR ahora
                        </button>
                        <button id="btn-modo-lectura" onclick="alternarModoLectura()" aria-pressed="false" class="btn btn-modo-lectura btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold">
                            <i class="bi bi-brightness-high me-1"></i><span>Modo Lectura</span>
                        </button>
                    </div>

                    <p class="texto-slate small mt-2 mb-0">Presenta esta pantalla frente a la cámara de tu profesor durante la clase activa. El código se renueva solo cada 60 segundos.</p>

                    <!-- Código de respaldo oculto por defecto: solo para pruebas en la misma PC -->
                    <details class="acordeon-pruebas mt-3 pt-3 border-top text-start">
                        <summary><i class="bi bi-wrench-adjustable"></i> Modo Pruebas · Código de respaldo</summary>
                        <div class="mt-2">
                            <small class="texto-slate d-block mb-1">Solo para pruebas en la misma PC, sin cámara. No compartas este código: cualquiera con él puede registrar tu asistencia.</small>
                            <code id="qr-token-respaldo" class="user-select-all bg-light px-2 py-1 rounded border small text-dark">—</code>
                        </div>
                    </details>
                </div>
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

        // Indicador visual de expiracion: solo lectura de segundosRestantes, no altera la logica del temporizador
        setInterval(function () {
            const s = typeof segundosRestantes === 'number' ? segundosRestantes : 0;
            const barra = document.getElementById('barra-expira');
            const pildora = document.getElementById('qr-timer');
            const enAlerta = s <= 10;
            if (barra) {
                barra.style.width = Math.max(0, Math.min(100, (s / 60) * 100)) + '%';
                barra.style.background = enAlerta ? '#D97706' : '#2563EB';
            }
            if (pildora) pildora.classList.toggle('expira-amarillo', enAlerta);
        }, 500);

        // Modo Lectura / Alto Contraste: agranda el QR y pone fondo blanco puro
        function alternarModoLectura() {
            const tarjeta = document.getElementById('tarjeta-qr');
            const boton = document.getElementById('btn-modo-lectura');
            if (!tarjeta || !boton) return;
            const activo = tarjeta.classList.toggle('modo-lectura');
            boton.setAttribute('aria-pressed', activo ? 'true' : 'false');
            const icono = boton.querySelector('i');
            if (icono) icono.className = activo ? 'bi bi-arrows-angle-contract me-1' : 'bi bi-brightness-high me-1';
            const etiqueta = boton.querySelector('span');
            if (etiqueta) etiqueta.textContent = activo ? 'Salir de Modo Lectura' : 'Modo Lectura';
        }

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
                        const tarjeta = document.getElementById('tarjeta-qr');
                        if (tarjeta) tarjeta.classList.add('qr-confirmado');
                    }
                })
                .catch(function () {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            generarNuevoQR();
            verificarConfirmacionEscaneo();
            setInterval(verificarConfirmacionEscaneo, 4000);
        });
    </script>
</body>
</html>
