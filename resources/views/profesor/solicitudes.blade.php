<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Alumnos - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-person-lines-fill me-2"></i>SAE QR | Solicitudes de Inscripción</a>
            <a href="{{ url('/profesor/dashboard') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container my-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-0">Validación de Alumnos en Grupos</h2>
            <p class="text-muted mt-1">Aprueba o rechaza las solicitudes de los estudiantes para habilitar su pase de lista con QR.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Alumno</th>
                            <th>Correo</th>
                            <th>Grupo Solicitado</th>
                            <th>Materia</th>
                            <th class="text-center">Estado Actual</th>
                            <th class="text-center pe-4">Decisión del Profesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($solicitudes->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2 opacity-50"></i>
                                    No hay solicitudes de inscripción registradas por el momento.
                                </td>
                            </tr>
                        @else
                            @foreach($solicitudes as $sol)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $sol->alumno->name }}</td>
                                    <td class="text-muted">{{ $sol->alumno->email }}</td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary">{{ $sol->grupo->nombre_grupo }}</span></td>
                                    <td>{{ $sol->materia->nombre_materia }}</td>
                                    <td class="text-center">
                                        @if($sol->estado === 'pendiente')
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @elseif($sol->estado === 'aceptado')
                                            <span class="badge bg-success">Aceptado</span>
                                        @else
                                            <span class="badge bg-danger">Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <!-- Botón Aceptar -->
                                        <form action="{{ route('profesor.solicitudes.actualizar', $sol->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="aceptado">
                                            <button type="submit" class="btn btn-sm btn-success rounded-3 me-1" {{ $sol->estado === 'aceptado' ? 'disabled' : '' }}>
                                                <i class="bi bi-check-lg me-1"></i>Aceptar
                                            </button>
                                        </form>

                                        <!-- Botón Rechazar -->
                                        <form action="{{ route('profesor.solicitudes.actualizar', $sol->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" {{ $sol->estado === 'rechazado' ? 'disabled' : '' }}>
                                                <i class="bi bi-x-lg me-1"></i>Rechazar
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