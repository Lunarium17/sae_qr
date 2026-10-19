<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Profesor - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    @php
        $gruposCount = \App\Models\Grupo::count();
        $sesionesActivasCount = \App\Models\SesionClase::where('profesor_id', auth()->id())->where('estado', 'activa')->count();
        $solicitudesPendientes = \App\Models\Matricula::where('estado', 'pendiente')->count();
        
        $totalRegistros = \App\Models\RegistroAsistencia::count();
        $asistenciasEfectivas = \App\Models\RegistroAsistencia::whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
        $porcentajeAsistencia = $totalRegistros > 0 ? round(($asistenciasEfectivas / $totalRegistros) * 100) : 0;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-qr-code-scan me-2"></i>SAE QR | Panel de Profesor</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Tarjetas con datos en tiempo real desde MariaDB -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <h5 class="text-muted fs-6">Mis Grupos</h5>
                    <h3 class="fw-bold text-dark">{{ $gruposCount }} Registrados</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <h5 class="text-muted fs-6">Sesiones Activas</h5>
                    <h3 class="fw-bold text-primary">{{ $sesionesActivasCount }} Abiertas</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <h5 class="text-muted fs-6">Asistencia Global</h5>
                    <h3 class="fw-bold text-success">{{ $porcentajeAsistencia }} %</h3>
                </div>
            </div>
        </div>

        <!-- Panel central de acciones -->
        <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center">
            <h2 class="fw-bold text-dark">Bienvenido, {{ auth()->user()->name }}</h2>
            <p class="text-muted lead mt-3">Desde este panel podrás gestionar tus materias, abrir sesiones de clase y <strong>utilizar tu cámara para escanear los códigos QR de tus estudiantes</strong>.</p>
            <hr class="my-4">
            
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('sesiones.index') }}" class="btn btn-primary rounded-3 px-4 py-2">
                    <i class="bi bi-camera-video me-2"></i>Iniciar Sesión y Escanear
                </a>
                <a href="{{ url('/profesor/grupos') }}" class="btn btn-outline-secondary rounded-3 px-4 py-2">
                    <i class="bi bi-card-list me-2"></i>Gestión de Grupos
                </a>
                <a href="{{ url('/profesor/materias') }}" class="btn btn-outline-dark rounded-3 px-4 py-2">
                    <i class="bi bi-journal-bookmark-fill me-2"></i>Gestión de Materias
                </a>
                <a href="{{ route('profesor.solicitudes') }}" class="btn btn-warning rounded-3 px-4 py-2">
                    <i class="bi bi-person-lines-fill me-2"></i>Solicitudes de Alumnos
                    @if($solicitudesPendientes > 0)
                        <span class="badge bg-danger ms-1">{{ $solicitudesPendientes }}</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Módulos del Sprint 6: Reportes y Justificantes -->
        <div class="row my-4 g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark"><i class="bi bi-file-earmark-pdf me-2"></i>Exportación y Reportes</h5>
                    <p class="text-muted small">Genera consolidados de asistencia semanales o mensuales.</p>

                    <!-- Botón para ir al módulo de reportes (sprint 6) -->
                    <a href="{{ route('profesor.reportes') }}" class="btn btn-outline-primary btn-sm rounded-3 mt-auto">
                        <i class="bi bi-bar-chart-line me-1"></i>Ver y Exportar Reportes (PDF / Excel)
                    </a>

                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark"><i class="bi bi-envelope-paper me-2"></i>Permisos y Justificantes</h5>
                    <p class="text-muted small">Revisa la carga digital de comprobantes para realizar el ajuste manual de inasistencias.</p>

                    <!-- Botón para revisar justificantes (sprint 6) -->
                    <a href="{{ route('profesor.justificantes') }}" class="btn btn-outline-info btn-sm rounded-3 mt-auto">
                        <i class="bi bi-envelope-paper me-1"></i>Revisar Justificantes Pendientes
                    </a>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>