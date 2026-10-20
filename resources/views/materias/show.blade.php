<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $materia->nombre_materia }} | Alumnos Inscritos - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ $materia->grupo_id ? route('grupos.show', $materia->grupo_id) : route('grupos.index') }}">
                <i class="bi bi-journal-bookmark-fill me-2"></i>SAE QR | {{ $materia->nombre_materia }}
            </a>
            <div class="d-flex align-items-center gap-2">
                @if ($materia->grupo_id)
                    <a href="{{ route('grupos.show', $materia->grupo_id) }}" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Grupo {{ $materia->grupo->nombre_grupo }}
                    </a>
                @else
                    <a href="{{ route('grupos.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Grupos
                    </a>
                @endif
                <a href="{{ route('profesor.dashboard') }}" class="btn btn-outline-secondary btn-sm text-light">
                    <i class="bi bi-house-door me-1"></i>Panel Principal
                </a>
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

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- 1. Encabezado de la Materia y Acciones Rápidas -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill">{{ $materia->codigo_materia }}</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $materia->nombre_materia }}</h3>
                        <a href="{{ route('materias.edit', $materia->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-3 ms-1">
                            <i class="bi bi-pencil-square me-1"></i>Editar Materia
                        </a>
                    </div>
                    @if ($materia->grupo)
                        <p class="text-muted mb-0 mt-2">
                            <span class="me-3"><i class="bi bi-people-fill me-1 text-primary"></i><strong>Grupo:</strong> {{ $materia->grupo->nombre_grupo }}</span>
                            <span class="me-3"><i class="bi bi-mortarboard-fill me-1 text-secondary"></i><strong>Grado:</strong> {{ $materia->grupo->grado }}</span>
                            <span class="me-3"><i class="bi bi-clock-fill me-1 text-secondary"></i><strong>Turno:</strong> {{ $materia->grupo->turno }}</span>
                            <span><i class="bi bi-calendar-range-fill me-1 text-secondary"></i><strong>Periodo:</strong> {{ $materia->grupo->periodo_lectivo }}</span>
                        </p>
                    @endif
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="px-3 py-2 bg-light rounded-3 border text-center">
                        <div class="fs-5 fw-bold text-success">{{ $materia->matriculas->where('estado', 'aceptado')->count() }}</div>
                        <div class="small text-muted">Alumnos Aceptados</div>
                    </div>
                    <div class="px-3 py-2 bg-light rounded-3 border text-center">
                        <div class="fs-5 fw-bold text-warning text-dark">{{ $materia->matriculas->where('estado', 'pendiente')->count() }}</div>
                        <div class="small text-muted">Pendientes</div>
                    </div>
                    <a href="{{ route('sesiones.index', ['materia_id' => $materia->id]) }}" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-qr-code-scan me-2"></i>Iniciar Clase QR
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- 2. Formulario Lateral: Inscribir Alumno Manualmente -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-person-plus-fill me-2 text-primary"></i>Inscribir Alumno Manualmente
                    </h5>
                    <p class="text-muted small mb-4">
                        Agrega directamente a un estudiante registrado a la materia <strong>{{ $materia->nombre_materia }}</strong> sin esperar a que envíe solicitud.
                    </p>

                    @php
                        $idsYaAceptados = $materia->matriculas->where('estado', 'aceptado')->pluck('user_id')->toArray();
                    @endphp

                    <form action="{{ route('profesor.matriculas.manual') }}" method="POST">
                        @csrf
                        <input type="hidden" name="materia_id" value="{{ $materia->id }}">
                        <input type="hidden" name="grupo_id" value="{{ $materia->grupo_id }}">

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Seleccionar Estudiante</label>
                            <select name="user_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>-- Elige un alumno --</option>
                                @foreach ($alumnosDisponibles as $alumno)
                                    @if (!in_array($alumno->id, $idsYaAceptados))
                                        <option value="{{ $alumno->id }}">
                                            {{ $alumno->name }} ({{ $alumno->email }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text mt-2">
                                El alumno quedará inscrito automáticamente con estado <strong>Aceptado</strong> para el pase de lista QR.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-2"></i>Inscribir en esta Materia
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. Tabla de Alumnos Inscritos y Resumen de Asistencia en esta Materia -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-people-fill me-2 text-dark"></i>Padrón de Alumnos de la Materia
                        </h5>
                        <span class="badge bg-dark rounded-pill">{{ $materia->matriculas->count() }} Registros</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Alumno</th>
                                    <th class="text-center">Asistencias / Faltas</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Gestión de Inscripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($materia->matriculas as $matricula)
                                    @php
                                        $regsAlumno = $registrosMateria->get($matricula->user_id, collect());
                                        $asistCount = $regsAlumno->where('estado_incidencia', 'asistencia')->count();
                                        $retCount   = $regsAlumno->where('estado_incidencia', 'retardo')->count();
                                        $permCount  = $regsAlumno->where('estado_incidencia', 'permiso')->count();
                                        $faltCount  = $regsAlumno->where('estado_incidencia', 'falta')->count();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $matricula->alumno->name ?? 'Alumno' }}</div>
                                            <div class="text-muted small">{{ $matricula->alumno->email ?? '-' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="mb-1">
                                                <span class="badge bg-success me-1" title="Asistencias">A: {{ $asistCount }}</span>
                                                <span class="badge bg-warning text-dark me-1" title="Retardos">R: {{ $retCount }}</span>
                                                <span class="badge bg-info text-dark me-1" title="Permisos">P: {{ $permCount }}</span>
                                                <span class="badge bg-danger" title="Faltas">F: {{ $faltCount }}</span>
                                            </div>
                                            @if ($faltCount >= 3)
                                                <span class="badge bg-danger"><i class="bi bi-x-octagon-fill me-1"></i>{{ $faltCount }} / 3 faltas (Límite Alcanzado)</span>
                                            @elseif ($faltCount === 2)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>2 / 3 faltas (En Advertencia)</span>
                                            @else
                                                <span class="small text-muted">{{ $faltCount }} / 3 faltas sin justificar</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($matricula->estado === 'aceptado')
                                                <span class="badge bg-success">Aceptado</span>
                                            @elseif ($matricula->estado === 'pendiente')
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @else
                                                <span class="badge bg-danger">Rechazado</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($matricula->estado !== 'aceptado')
                                                <form action="{{ route('profesor.solicitudes.actualizar', $matricula->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="aceptado">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-3 me-1" title="Aceptar en la materia">
                                                        <i class="bi bi-check-lg"></i> Aceptar
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($matricula->estado === 'pendiente')
                                                <form action="{{ route('profesor.solicitudes.actualizar', $matricula->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="rechazado">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-3 me-1" title="Rechazar solicitud">
                                                        <i class="bi bi-x-lg"></i> Rechazar
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('profesor.matriculas.destroy', $matricula->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas dar de baja a este alumno de esta materia?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Dar de baja de la materia">
                                                    <i class="bi bi-person-dash me-1"></i>Dar de Baja
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                            Aún no hay alumnos inscritos en esta materia. Puedes inscribirlos manualmente desde el panel izquierdo.
                                        </td>
                                    </tr>
                                @endforelse
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

