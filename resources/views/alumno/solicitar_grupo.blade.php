<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Grupo - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-person-badge me-2"></i>SAE QR | Inscripción a Grupos</a>
            <a href="{{ url('/alumno/dashboard') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver a mi Panel
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

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Formulario para solicitar inscripción -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-1">Solicitar Pertenencia a Grupo</h5>
                    <p class="text-muted small mb-4">Selecciona tu grupo y asignatura. El profesor validará tu solicitud.</p>

                    <form action="{{ route('alumno.solicitar.guardar') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Selecciona el Grupo</label>
                            <select name="grupo_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>Elige un grupo...</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }} - {{ $grupo->grado }} ({{ $grupo->turno }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Selecciona la Materia</label>
                            <select name="materia_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>Elige una materia...</option>
                                @foreach($materias as $materia)
                                    <option value="{{ $materia->id }}">[{{ $materia->codigo_materia }}] {{ $materia->nombre_materia }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-send-fill me-2"></i>Enviar Solicitud
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historial de Solicitudes y Materias Inscritas -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3">Estado de mis Solicitudes e Inscripciones</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Grupo</th>
                                    <th>Materia</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($misMatriculas->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Aún no has enviado solicitudes de inscripción.</td>
                                    </tr>
                                @else
                                    @foreach($misMatriculas as $mat)
                                        <tr>
                                            <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary">{{ $mat->grupo->nombre_grupo }}</span></td>
                                            <td class="fw-semibold">{{ $mat->materia->nombre_materia }}</td>
                                            <td class="text-center">
                                                @if($mat->estado === 'pendiente')
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pendiente</span>
                                                @elseif($mat->estado === 'aceptado')
                                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aceptado</span>
                                                @else
                                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rechazado</span>
                                                @endif
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>