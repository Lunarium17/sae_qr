<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-card-list me-2"></i>SAE QR | Gestión de Grupos</a>
            <div class="d-flex align-items-center">
                <!-- Botón para regresar al Dashboard del Profesor -->
                <a href="{{ url('/profesor/dashboard') }}" class="btn btn-outline-light btn-sm me-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Alerta de éxito al crear un grupo -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Encabezado de la sección -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">Mis Grupos Académicos</h2>
                <p class="text-muted mt-1">Administra los grupos asignados y matricula a tus estudiantes.</p>
            </div>
            <!-- Botón para crear un nuevo grupo -->
            <a href="{{ route('grupos.create') }}" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Grupo
            </a>
        </div>

        <!-- Tabla Responsiva de Grupos -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4 py-3">ID</th>
                                <th scope="col" class="py-3">Nombre del Grupo</th>
                                <th scope="col" class="py-3">Grado</th>
                                <th scope="col" class="py-3">Turno</th>
                                <th scope="col" class="py-3">Periodo Lectivo</th>
                                <th scope="col" class="text-center pe-4 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Verificamos si la variable $grupos está vacía -->
                            @if($grupos->isEmpty())
                                <!-- Diseño de "Estado Vacío" -->
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-folder-x display-1 mb-3 text-secondary opacity-50"></i>
                                            <h5 class="fw-bold">No hay grupos registrados aún</h5>
                                            <p>Comienza creando tu primer grupo académico haciendo clic en el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <!-- Si hay grupos, los iteramos con un foreach -->
                                @foreach($grupos as $grupo)
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">{{ $grupo->id }}</td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-2 fs-6">
                                                {{ $grupo->nombre_grupo }}
                                            </span>
                                        </td>
                                        <td>{{ $grupo->grado }}</td>
                                        <td>{{ $grupo->turno }}</td>
                                        <td>{{ $grupo->periodo_lectivo }}</td>
                                        <td class="text-center pe-4">
                                            <button class="btn btn-sm btn-outline-info rounded-3 me-1" title="Ver Alumnos" disabled><i class="bi bi-eye"></i></button>
                                            <button class="btn btn-sm btn-outline-warning rounded-3 me-1" title="Editar Grupo" disabled><i class="bi bi-pencil"></i></button>
                                            <form action="#" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar Grupo" disabled><i class="bi bi-trash"></i></button>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>