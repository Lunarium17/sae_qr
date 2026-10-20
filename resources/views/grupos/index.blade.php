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
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-card-list me-2"></i>SAE QR | Gestión de Grupos y Materias</a>
            <div class="d-flex align-items-center">
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
                <p class="text-muted mt-1">Administra tus grupos asignados, sus materias impartidas, alumnos inscritos y rendimiento de asistencia.</p>
            </div>
            <a href="{{ route('grupos.create') }}" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Grupo
            </a>
        </div>

        <!-- Tabla Responsiva de Grupos con Semáforo de Asistencia -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4 py-3">ID</th>
                                <th scope="col" class="py-3">Nombre del Grupo</th>
                                <th scope="col" class="py-3">Grado / Turno</th>
                                <th scope="col" class="py-3">Periodo</th>
                                <th scope="col" class="text-center py-3">Materias / Alumnos</th>
                                <th scope="col" class="py-3" style="min-width: 190px;">Índice de Asistencia</th>
                                <th scope="col" class="text-center pe-4 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($grupos->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-folder-x display-1 mb-3 text-secondary opacity-50"></i>
                                            <h5 class="fw-bold">No hay grupos registrados aún</h5>
                                            <p>Comienza creando tu primer grupo académico haciendo clic en el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($grupos as $grupo)
                                    @php
                                        $pct = $grupo->porcentaje_asistencia;
                                        $colorBarra = is_null($pct) ? 'secondary' : ($pct >= 85 ? 'success' : ($pct >= 70 ? 'warning' : 'danger'));
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">{{ $grupo->id }}</td>
                                        <td>
                                            <a href="{{ route('grupos.show', $grupo->id) }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-2 fs-6 text-decoration-none">
                                                {{ $grupo->nombre_grupo }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $grupo->grado }}</div>
                                            <span class="text-muted small">{{ $grupo->turno }}</span>
                                        </td>
                                        <td>{{ $grupo->periodo_lectivo }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-dark rounded-pill px-2 py-1 me-1">
                                                {{ $grupo->materias_count }} mat.
                                            </span>
                                            <span class="badge bg-info text-dark rounded-pill px-2 py-1">
                                                {{ $grupo->alumnos_aceptados_count }} alum.
                                            </span>
                                        </td>
                                        <td>
                                            @if (is_null($pct))
                                                <span class="text-muted small"><i class="bi bi-dash-circle me-1"></i>Sin sesiones registradas</span>
                                            @else
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="fw-bold text-{{ $colorBarra === 'warning' ? 'dark' : $colorBarra }}">{{ $pct }}%</span>
                                                    <span class="text-muted">{{ $grupo->total_registros_grupo }} pases</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $colorBarra }}" role="progressbar" data-width="{{ $pct }}"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('grupos.show', $grupo->id) }}" class="btn btn-sm btn-primary rounded-3 me-1" title="Gestionar Materias, Alumnos y Estadísticas">
                                                <i class="bi bi-journal-bookmark-fill me-1"></i>Gestionar Materias
                                            </a>
                                            <a href="{{ route('grupos.edit', $grupo->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-3 me-1" title="Editar Grupo">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('grupos.destroy', $grupo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este grupo y sus materias asociadas?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar Grupo">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-width]').forEach(function (el) {
                el.style.width = el.getAttribute('data-width') + '%';
            });
        });
    </script>
</body>
</html>
