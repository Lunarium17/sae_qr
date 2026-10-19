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
        $userId = auth()->id();
        $pendientesCount = \App\Models\Matricula::where('user_id',$userId)->where('estado', 'pendiente')->count();
        $asistenciasCount = \App\Models\RegistroAsistencia::where('user_id',$userId)->where('estado_incidencia', 'asistencia')->count();
        $retardosCount = \App\Models\RegistroAsistencia::where('user_id',$userId)->where('estado_incidencia', 'retardo')->count();
        $faltasCount = \App\Models\RegistroAsistencia::where('user_id',$userId)->where('estado_incidencia', 'falta')->count();
        $permisosCount = \App\Models\RegistroAsistencia::where('user_id',$userId)->where('estado_incidencia', 'permiso')->count();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-person-badge me-2"></i>SAE QR | Panel de Alumno</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h3 class="fw-bold text-dark">Hola, {{ auth()->user()->name }}</h3>
            <p class="text-muted mb-2">Aquí podrás consultar las materias a las que estás inscrito y visualizar el código QR que deberás presentar a tu profesor.</p>
            <div class="mt-2">
                <a href="{{ route('alumno.solicitar') }}" class="btn btn-outline-primary btn-sm rounded-3">
                    <i class="bi bi-search me-1"></i>Buscar y Solicitar Grupo
                </a>
                <span class="badge bg-secondary ms-2">{{ $pendientesCount }} Solicitudes Pendientes</span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna del Código QR Dinámico -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                    <h5 class="text-dark mb-3 fw-bold">Mi Código QR Temporal</h5>
                    
                    <div class="p-3 bg-white border rounded-4 d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 230px; height: 230px;">
                        <div id="qrcode"></div>
                    </div>

                    <div class="mb-2">
                        <span id="qr-timer" class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
                            Actualizando en 60s
                        </span>
                    </div>

                    <button onclick="generarNuevoQR()" class="btn btn-sm btn-outline-secondary rounded-pill mx-auto px-3 mt-1">
                        <i class="bi bi-arrow-clockwise me-1"></i>Renovar QR ahora
                    </button>

                    <p class="text-muted small mt-3 mb-0">Presenta esta pantalla frente a la cámara de tu profesor durante la clase activa.</p>
                </div>
            </div>
            
            <!-- Columna de Resumen de Asistencia -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="text-dark fw-bold mb-4">Resumen de Asistencia</h5>

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
                            <span class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Faltas</span>
                            <span class="badge bg-danger rounded-pill px-3">{{ $faltasCount }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-primary"><i class="bi bi-info-circle-fill me-2"></i>Permiso / Justificado</span>
                            <span class="badge bg-primary rounded-pill px-3">{{ $permisosCount }}</span>
                        </li>
                    </ul>

                    <hr class="my-4">
                    <div class="d-grid gap-3">

                        <!-- Botón para ver el calendario (sprint 6) -->
                        <a href="{{ route('alumno.calendario') }}" class="btn btn-outline-primary rounded-3 text-start d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar3 me-2"></i>Ver Calendario Mensual</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <!-- Botón para cargar justificantes (sprint 6)-->
                        <a href="{{ route('alumno.justificantes') }}" class="btn btn-outline-secondary rounded-3 text-start d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-upload me-2"></i>Carga Digital de Justificante</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Librería para dibujar el QR en pantalla -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        let segundosRestantes = 60;
        let intervaloReloj;

        function generarNuevoQR() {
            fetch("{{ route('qr.generar') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const contenedor = document.getElementById('qrcode');
                        contenedor.innerHTML = ''; // Limpiar QR anterior
                        new QRCode(contenedor, {
                            text: data.qr_token,
                            width: 190,
                            height: 190,
                            colorDark : "#000000",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.M
                        });
                        segundosRestantes = 60;
                    }
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

        // Generar QR al abrir el panel
        document.addEventListener('DOMContentLoaded', generarNuevoQR);
    </script>
</body>
</html>