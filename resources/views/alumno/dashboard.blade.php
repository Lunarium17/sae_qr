<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Alumno - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Panel de Alumno</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                    <h5 class="text-muted mb-3">Mi Código QR Personal</h5>
                    <div class="p-3 bg-light border rounded-3 d-inline-block mx-auto mb-3" style="width: 150px; height: 150px;">
                        <span class="text-muted small d-flex align-items-center justify-content-center h-100">[QR Dinámico]</span>
                    </div>
                    <p class="text-muted small mb-0">Usa este código o escanea el de tu profesor según la dinámica de clase.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="text-dark fw-bold mb-3">Resumen de Asistencia</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Asistencias registradas <span class="badge bg-success rounded-pill">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Retardos <span class="badge bg-warning text-dark rounded-pill">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Faltas <span class="badge bg-danger rounded-pill">0</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h3 class="fw-bold text-dark">Hola, {{ auth()->user()->name }}</h3>
            <p class="text-muted">Aquí podrás consultar las materias a las que estás inscrito y verificar tus registros de asistencia en tiempo real.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>