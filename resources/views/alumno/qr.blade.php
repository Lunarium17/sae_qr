<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Código QR Temporal - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
</head>
<body>

    @php
        $alumno = \Illuminate\Support\Facades\Auth::user();
        $inicialesAlumno = mb_strtoupper(implode('', array_map(fn ($palabra) => mb_substr($palabra, 0, 1), array_slice(preg_split('/\s+/', trim($alumno->name ?? 'U')), 0, 2))));
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
                <div class="card rounded-4 p-4 bg-white text-center">
                    <h5 class="text-dark mb-3 fw-bold">Mi Código QR Temporal</h5>

                    <!-- Confirmación en Tiempo Real cuando el QR del Alumno es escaneado -->
                    <div id="qr-confirmacion-escaneo" data-estado-url="{{ route('qr.estado') }}" class="alert alert-success border-success border-2 rounded-4 py-2 px-3 mb-3 d-none shadow-sm text-start">
                        <div class="d-flex align-items-center">
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
                        <small class="texto-slate d-block mb-1">Código de respaldo (para pruebas en la misma PC):</small>
                        <code id="qr-token-respaldo" class="user-select-all bg-light px-2 py-1 rounded border small text-dark">—</code>
                    </div>

                    <p class="texto-slate small mt-2 mb-0">Presenta esta pantalla frente a la cámara de tu profesor durante la clase activa. El código se renueva solo cada 60 segundos.</p>
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

        document.addEventListener('DOMContentLoaded', function () {
            generarNuevoQR();
            verificarConfirmacionEscaneo();
            setInterval(verificarConfirmacionEscaneo, 4000);
        });
    </script>
</body>
</html>
