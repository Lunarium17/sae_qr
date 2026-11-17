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
        /* Credencial Escolar Digital: franja azul institucional + zona blanca del QR */
        .credencial {
            max-width: 380px;
            margin-inline: auto;
            background: #FFFFFF;
            border: 1px solid rgba(203, 213, 225, .9);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: var(--panel-shadow);
        }
        .credencial-header {
            background: linear-gradient(135deg, #0B2447 0%, #19376D 60%, #2F5BD7 100%);
            padding: 1.05rem 1.25rem 1.15rem;
            text-align: center;
        }
        .cintillo-clase {
            display: inline-flex; align-items: center; gap: .45rem;
            border-radius: 50rem; padding: .3rem .85rem;
            font-size: .72rem; font-weight: 600; max-width: 100%;
            background: rgba(255,255,255,.15); color: #FFFFFF;
            border: 1px solid rgba(255,255,255,.30);
        }
        .punto-pulso { width: 7px; height: 7px; border-radius: 50%; background: #34D399; flex-shrink: 0; animation: pulso 1.6s infinite; }
        @keyframes pulso {
            0%   { box-shadow: 0 0 0 0 rgba(52,211,153,.55); }
            70%  { box-shadow: 0 0 0 6px rgba(52,211,153,0); }
            100% { box-shadow: 0 0 0 0 rgba(52,211,153,0); }
        }
        .avatar-credencial {
            width: 54px; height: 54px; border-radius: 50%;
            background: #FFFFFF; color: #19376D;
            font-weight: 800; font-size: 1.05rem;
            display: inline-flex; align-items: center; justify-content: center;
            border: 3px solid rgba(255,255,255,.40); flex-shrink: 0;
        }
        .nombre-credencial { color: #FFFFFF; font-weight: 700; font-size: 1.08rem; line-height: 1.25; }
        .rol-credencial { color: rgba(255,255,255,.75); font-size: .78rem; }
        .credencial-body { padding: 1.25rem 1.25rem 1.35rem; text-align: center; }

        .marco-qr {
            width: min(250px, calc(100vw - 56px));
            height: auto;
            aspect-ratio: 1;
            padding: 20px;
            margin-inline: auto;
            display: flex; align-items: center; justify-content: center;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 1.25rem;
            box-shadow: 0 4px 10px -4px rgba(15,23,42,.12);
        }
        .zona-expira { width: min(250px, calc(100vw - 56px)); margin: .9rem auto 0; }
        .pildora-expira {
            display: inline-flex; align-items: center;
            border-radius: 50rem; padding: .38rem .9rem;
            font-size: .78rem; font-weight: 600;
            background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;
        }
        .contenedor-expira { height: 7px; border-radius: 50rem; background: #E2E8F0; overflow: hidden; margin-top: .55rem; }
        #barra-expira { height: 100%; width: 100%; border-radius: 50rem; background: #2563EB; transition: width .9s linear, background .3s ease; }
        #qr-timer.expira-amarillo { background: #FFFBEB !important; color: #B45309 !important; border-color: #FCD34D !important; }

        #qr-confirmacion-escaneo {
            display: inline-flex; align-items: center; gap: .5rem;
            max-width: 100%; margin-inline: auto;
            border-radius: 50rem; padding: .45rem .95rem;
            background: #ECFDF5; border: 1px solid #A7F3D0;
            text-align: left;
        }
        #qr-confirmacion-escaneo i { color: #059669; font-size: 1rem; flex-shrink: 0; }
        .confirmacion-titulo { color: #047857; font-weight: 700; font-size: .78rem; line-height: 1.3; }
        .confirmacion-detalle { color: #065F46; font-size: .72rem; line-height: 1.3; }

        .aviso-sesion {
            border-radius: .75rem; padding: .55rem .75rem;
            background: #FFFBEB; border: 1px solid #FDE68A;
            color: #92400E; font-size: .74rem; line-height: 1.45;
        }

        .btn-primario-credencial { background: #2563EB; color: #FFFFFF; border: 1px solid #2563EB; }
        .btn-primario-credencial:hover, .btn-primario-credencial:focus { background: #1D4ED8; color: #FFFFFF; border-color: #1D4ED8; }
        .btn-modo-lectura[aria-pressed="true"] { background: #2563EB !important; color: #FFFFFF !important; border-color: #2563EB !important; }

        .instruccion { color: #64748B; font-size: .78rem; line-height: 1.5; max-width: 270px; margin: .95rem auto 0; }

        #tarjeta-qr.qr-confirmado { box-shadow: 0 0 0 3px rgba(5,150,105,.18), var(--panel-shadow); }

        /* Modo Lectura: QR mas grande (260px utiles) con fondo blanco puro */
        #tarjeta-qr.modo-lectura .marco-qr {
            width: min(300px, calc(100vw - 56px));
            border-color: #CBD5E1;
            box-shadow: 0 8px 20px -6px rgba(15,23,42,.18);
        }
        #tarjeta-qr.modo-lectura #qrcode canvas,
        #tarjeta-qr.modo-lectura #qrcode img { transform: scale(1.2381); transform-origin: center; }

        /* Modo Pruebas: fuera de la tarjeta, muy discreto */
        .modo-pruebas { max-width: 380px; margin-top: .8rem; }
        .modo-pruebas summary { cursor: pointer; color: #94A3B8; font-size: .72rem; font-weight: 600; list-style: none; display: inline-flex; align-items: center; gap: .35rem; }
        .modo-pruebas summary::-webkit-details-marker { display: none; }
        .modo-pruebas summary:hover { color: #64748B; }
        .modo-pruebas small { color: #64748B; }
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

        // Linea de rol bajo el nombre: matricula real, grupo, o rol de la plataforma (nunca "Sin matricula")
        $matriculaGrupoQr = \App\Models\Matricula::with('grupo')->where('user_id', $alumno->id)->where('estado', 'aceptado')->whereNotNull('grupo_id')->first();
        if (!empty($alumno->matricula_codigo)) {
            $etiquetaRolQr = 'Matrícula: ' . $alumno->matricula_codigo;
        } elseif ($matriculaGrupoQr && $matriculaGrupoQr->grupo) {
            $etiquetaRolQr = 'Alumno · Grupo ' . $matriculaGrupoQr->grupo->nombre_grupo;
        } else {
            $etiquetaRolQr = 'Alumno · SAE QR';
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
        <div id="tarjeta-qr" class="credencial">
            <!-- Franja superior azul: identidad separada del QR -->
            <div class="credencial-header">
                <span class="cintillo-clase">
                    @if ($sesionQrActiva)
                        <span class="punto-pulso"></span>
                        <span>En curso · {{ $sesionQrActiva->materia->nombre_materia }}{{ $sesionQrActiva->materia->grupo ? ' · Grupo ' . $sesionQrActiva->materia->grupo->nombre_grupo : '' }}</span>
                    @else
                        <i class="bi bi-patch-check"></i>
                        <span>Credencial vigente · {{ now()->format('d/m/Y') }}</span>
                    @endif
                </span>
                <div class="d-flex align-items-center justify-content-center gap-3 mt-2">
                    <span class="avatar-credencial">{{ $inicialesAlumno }}</span>
                    <div class="text-start">
                        <div class="nombre-credencial">{{ $alumno->name }}</div>
                        <div class="rol-credencial">{{ $etiquetaRolQr }}</div>
                    </div>
                </div>
            </div>

            <div class="credencial-body">
                <!-- Aparece unicamente cuando el estado real del token es confirmado/escaneado -->
                <div id="qr-confirmacion-escaneo" data-estado-url="{{ route('qr.estado') }}" class="d-none mb-3" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <div class="confirmacion-titulo">Asistencia registrada</div>
                        <div id="qr-confirmacion-detalle" class="confirmacion-detalle"></div>
                    </div>
                </div>

                <div id="qr-error-sesion" class="aviso-sesion d-none mb-3">
                    Tu sesión de Alumno cambió porque iniciaste sesión como Profesor en otra pestaña del mismo navegador. Abre el perfil de Profesor o Alumno en una <strong>ventana de incógnito</strong> o en otro dispositivo.
                </div>

                <!-- Zona central del QR -->
                <div class="marco-qr">
                    <div id="qrcode" data-qr-url="{{ route('qr.generar') }}"></div>
                </div>

                <!-- Temporizador alineado con el marco del QR -->
                <div class="zona-expira">
                    <span id="qr-timer" class="pildora-expira">
                        Actualizando en 60s
                    </span>
                    <div class="contenedor-expira">
                        <div id="barra-expira"></div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <button onclick="generarNuevoQR()" class="btn btn-primario-credencial btn-sm rounded-3 px-3 fw-semibold">
                        <i class="bi bi-arrow-clockwise me-1"></i>Renovar QR ahora
                    </button>
                    <button id="btn-modo-lectura" onclick="alternarModoLectura()" aria-pressed="false" class="btn btn-modo-lectura btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold">
                        <i class="bi bi-brightness-high me-1"></i><span>Modo Lectura</span>
                    </button>
                </div>

                <p class="instruccion mb-0">Presenta esta pantalla frente a la cámara de tu profesor durante la clase activa. El código se renueva solo cada 60 segundos.</p>
            </div>
        </div>

        <!-- Código de respaldo fuera de la tarjeta, discreto y oculto por defecto -->
        <div class="modo-pruebas">
            <details>
                <summary><i class="bi bi-wrench-adjustable"></i> Modo Pruebas · Código de respaldo</summary>
                <div class="mt-2 text-start">
                    <small class="d-block mb-1">Solo para pruebas en la misma PC, sin cámara. No compartas este código: cualquiera con él puede registrar tu asistencia.</small>
                    <code id="qr-token-respaldo" class="user-select-all bg-light px-2 py-1 rounded border small text-dark">—</code>
                </div>
            </details>
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
