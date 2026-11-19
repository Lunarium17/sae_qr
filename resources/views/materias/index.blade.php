<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Materias - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    @include('partials.ui_helpers')

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}"><i class="bi bi-journal-bookmark-fill me-2"></i>SAE QR | Gestión de Materias</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ url('/profesor/grupos') }}" class="btn btn-info text-dark fw-semibold btn-sm rounded-3">
                    <i class="bi bi-card-list me-1"></i>Ver Grupos
                </a>
                <a href="{{ url('/profesor/dashboard') }}" class="btn btn-light text-dark fw-semibold btn-sm rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger fw-semibold btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <h2 class="fw-bold text-dark mb-0">Catálogo de Materias</h2>
                <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Administra las asignaturas que impartes en la institución.">!</span>
            </div>
            <a href="{{ route('materias.create') }}" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Nueva Materia
            </a>
        </div>

        <!-- Tabla de Materias -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Código / Clave</th>
                            <th>Nombre de la Materia</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($materias->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-journal-x display-1 mb-3 text-secondary opacity-50"></i>
                                        <h5 class="fw-bold">No hay materias registradas aún</h5>
                                        <p>Comienza registrando tu primera asignatura haciendo clic en el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach($materias as $materia)
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">{{ $materia->id }}</td>
                                    <td><span class="badge bg-dark rounded-pill px-3 py-2">{{ $materia->codigo_materia }}</span></td>
                                    <td class="fw-semibold text-dark">{{ $materia->nombre_materia }}</td>
                                    <td class="text-center pe-4">
                                        <form action="{{ route('materias.destroy', $materia->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta materia?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-3" title="Eliminar Materia">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
