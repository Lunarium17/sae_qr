<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Profesor - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Panel de Profesor</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <h5 class="text-muted fs-6">Mis Grupos</h5>
                    <h3 class="fw-bold text-dark">0 Asignados</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <h5 class="text-muted fs-6">Sesiones Activas</h5>
                    <h3 class="fw-bold text-primary">0 Abiertas</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <h5 class="text-muted fs-6">Asistencia Global</h5>
                    <h3 class="fw-bold text-success">-- %</h3>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold text-dark">Bienvenido, {{ auth()->user()->name }}</h2>
            <p class="text-muted">Desde este panel podrás gestionar tus materias, abrir sesiones de clase y proyectar el código QR temporal de asistencia para tus alumnos.</p>
            <hr>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-3" disabled>Iniciar Nueva Sesión de Clase</button>
                <button class="btn btn-outline-secondary rounded-3" disabled>Ver Historial de Asistencias</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>