<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupo {{ $grupo->nombre_grupo }} | Gestión y Estadísticas - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    @php
        $nombresMateriasChart = $grupo->materias->pluck('nombre_materia')->toArray();
        $porcentajesMateriasChart = $grupo->materias->map(fn($m) => $m->porcentaje_asistencia ?? 0)->toArray();
        $colorGrupoSemaforo = $statsGrupo['porcentaje'] >= 85 ? 'success' : ($statsGrupo['porcentaje'] >= 70 ? 'warning' : 'danger');
    @endphp

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('grupos.index') }}">
                <i class="bi bi-collection me-2"></i>SAE QR | Grupo {{ $grupo->nombre_grupo }}
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('grupos.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Mis Grupos
                </a>
                <a href="{{ route('profesor.dashboard') }}" class="btn btn-outline-secondary btn-sm text-light">
                    <i class="bi bi-house-door me-1"></i>Panel Principal
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Alertas de retroalimentación -->
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

        <!-- 1. Datos del Grupo y KPIs Superiores -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">{{ $grupo->nombre_grupo }}</span>
                        <h3 class="fw-bold text-dark mb-0">Información y Estadísticas del Grupo</h3>
                        <a href="{{ route('grupos.edit', $grupo->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-3 ms-2">
                            <i class="bi bi-pencil-square me-1"></i>Editar Grupo
                        </a>
                    </div>
                    <p class="text-muted mb-0 mt-2">
                        @if ($grupo->carrera)
                            <span class="me-3"><i class="bi bi-building me-1 text-primary"></i><strong>Carrera:</strong> {{ $grupo->carrera }}</span>
                        @endif
                        <span class="me-3"><i class="bi bi-mortarboard-fill me-1 text-secondary"></i><strong>Semestre:</strong> {{ $grupo->grado }}</span>
                        <span class="me-3"><i class="bi bi-clock-fill me-1 text-secondary"></i><strong>Turno:</strong> {{ $grupo->turno }}</span>
                        <span><i class="bi bi-calendar-range-fill me-1 text-secondary"></i><strong>Periodo Semestral:</strong> {{ $grupo->periodo_lectivo }}</span>
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 text-center">
                    <div class="px-3 py-2 bg-light rounded-3 border">
                        <div class="fs-4 fw-bold text-primary">{{ $grupo->materias->count() }}</div>
                        <div class="small text-muted">Materias</div>
                    </div>
                    <div class="px-3 py-2 bg-light rounded-3 border">
                        <div class="fs-4 fw-bold text-success">{{ $grupo->matriculas->where('estado', 'aceptado')->count() }}</div>
                        <div class="small text-muted">Inscritos</div>
                    </div>
                    <div class="px-3 py-2 bg-light rounded-3 border">
                        <div class="fs-4 fw-bold text-dark">{{ $statsGrupo['total_sesiones'] }}</div>
                        <div class="small text-muted">Sesiones</div>
                    </div>
                    <div class="px-3 py-2 bg-light rounded-3 border">
                        <div class="fs-4 fw-bold text-{{ $colorGrupoSemaforo }}">{{ $statsGrupo['porcentaje'] }}%</div>
                        <div class="small text-muted">Asistencia</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Gráficas Estadísticas del Grupo (Chart.js) -->
        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Incidencias en {{ $grupo->nombre_grupo }}</h6>
                        <span class="badge bg-dark rounded-pill">{{ $statsGrupo['total_registros'] }} registros</span>
                    </div>
                    @if ($statsGrupo['total_registros'] > 0)
                        <div class="mx-auto" style="max-width: 220px;">
                            <canvas id="chartIncidenciasGrupo"></canvas>
                        </div>
                        <div class="row text-center mt-3 g-2">
                            <div class="col-3"><span class="badge bg-success w-100 py-1">A: {{ $statsGrupo['asistencias'] }}</span></div>
                            <div class="col-3"><span class="badge bg-warning text-dark w-100 py-1">R: {{ $statsGrupo['retardos'] }}</span></div>
                            <div class="col-3"><span class="badge bg-info text-dark w-100 py-1">P: {{ $statsGrupo['permisos'] }}</span></div>
                            <div class="col-3"><span class="badge bg-danger w-100 py-1">F: {{ $statsGrupo['faltas'] }}</span></div>
                        </div>
                    @else
                        <div class="text-center text-muted my-auto py-4">
                            <i class="bi bi-bar-chart fs-1 d-block mb-1 opacity-50"></i>
                            Sin asistencias registradas aún en este grupo.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-fill me-2 text-success"></i>% de Asistencia por Materia del Grupo</h6>
                        <span class="small text-muted">Semáforo de rendimiento</span>
                    </div>
                    @if ($grupo->materias->isNotEmpty())
                        <div style="height: 230px;">
                            <canvas id="chartMateriasGrupo"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted my-auto py-4">
                            <i class="bi bi-journal-x fs-1 d-block mb-1 opacity-50"></i>
                            Registra materias en el grupo para comparar su porcentaje de asistencia.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Panel Unificado: Formulario Lateral (Izquierda) + Tabla de Materias del Grupo (Derecha) -->
        <div class="row g-4 mb-4">
            <!-- Formulario lateral para agregar materias al grupo -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Agregar Materia al Grupo
                    </h5>
                    <p class="text-muted small mb-4">
                        Registra una nueva asignatura que impartes dentro del grupo <strong>{{ $grupo->nombre_grupo }} ({{ $grupo->turno }})</strong>.
                    </p>

                    <form action="{{ route('materias.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Código / Clave de la Materia</label>
                            <input type="text" name="codigo" value="{{ old('codigo') }}" class="form-control rounded-3" placeholder="Ej. ING-301" maxlength="30" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nombre de la Materia</label>
                            <input type="text" name="nombre_materia" value="{{ old('nombre_materia') }}" class="form-control rounded-3" placeholder="Ej. Fundamentos de Ciberseguridad" maxlength="100" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-journal-plus me-2"></i>Guardar Materia en este Grupo
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabla de materias pertenecientes a este grupo con Semáforo de Asistencia -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-journal-bookmark-fill me-2 text-dark"></i>Materias Impartidas en {{ $grupo->nombre_grupo }}
                        </h5>
                        <span class="badge bg-dark rounded-pill">{{ $grupo->materias->count() }} Registradas</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Materia</th>
                                    <th class="text-center">Inscritos</th>
                                    <th style="min-width: 145px;">Asistencia</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($grupo->materias as $materia)
                                    @php
                                        $pctMat = $materia->porcentaje_asistencia;
                                        $colorMat = is_null($pctMat) ? 'secondary' : ($pctMat >= 85 ? 'success' : ($pctMat >= 70 ? 'warning' : 'danger'));
                                        $esMiMateria = is_null($materia->profesor_id) || (int) $materia->profesor_id === (int) \Illuminate\Support\Facades\Auth::id();
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark rounded-pill px-3 py-2">{{ $materia->codigo_materia }}</span>
                                        </td>
                                        <td>
                                            @if ($esMiMateria)
                                                <a href="{{ route('materias.show', $materia->id) }}" class="fw-semibold text-dark text-decoration-none">
                                                    {{ $materia->nombre_materia }}
                                                </a>
                                                <div><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small">Mi asignatura</span></div>
                                            @else
                                                <span class="fw-semibold text-secondary">{{ $materia->nombre_materia }}</span>
                                                <div class="small text-muted"><i class="bi bi-person-workspace me-1"></i>Prof. {{ $materia->profesor->name ?? 'Otro docente' }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($esMiMateria)
                                                <a href="{{ route('materias.show', $materia->id) }}" class="badge bg-info text-dark text-decoration-none rounded-pill px-3 py-2" title="Ver y gestionar alumnos de esta materia">
                                                    <i class="bi bi-people-fill me-1"></i>{{ $materia->matriculas->count() }}
                                                </a>
                                            @else
                                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2">
                                                    <i class="bi bi-people-fill me-1"></i>{{ $materia->matriculas->count() }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (is_null($pctMat))
                                                <span class="text-muted small">Sin sesiones</span>
                                            @else
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="fw-bold text-{{ $colorMat === 'warning' ? 'dark' : $colorMat }}">{{ $pctMat }}%</span>
                                                    <span class="text-muted">{{ $materia->total_registros }} reg.</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 7px;">
                                                    <div class="progress-bar bg-{{ $colorMat }}" role="progressbar" data-width="{{ $pctMat }}"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($esMiMateria)
                                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                                    <a href="{{ route('sesiones.index', ['materia_id' => $materia->id]) }}" class="btn btn-sm btn-primary rounded-3" title="Iniciar Pase de Lista QR para esta materia">
                                                        <i class="bi bi-qr-code-scan me-1"></i>Iniciar QR
                                                    </a>

                                                    <a href="{{ route('materias.show', $materia->id) }}" class="btn btn-sm btn-outline-dark rounded-3" title="Gestionar Alumnos de la Materia">
                                                        <i class="bi bi-people me-1"></i>Alumnos
                                                    </a>

                                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-3" data-bs-toggle="modal" data-bs-target="#modalEditarMateria-{{ $materia->id }}" title="Editar Materia">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>

                                                    <form action="{{ route('materias.destroy', $materia->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta materia del grupo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar Materia">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Modal para Editar Materia -->
                                                <div class="modal fade text-start" id="modalEditarMateria-{{ $materia->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0 shadow">
                                                            <div class="modal-header bg-warning text-dark border-0">
                                                                <h5 class="modal-title fw-bold">
                                                                    <i class="bi bi-pencil-square me-2"></i>Editar Materia
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('materias.update', $materia->id) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">
                                                                <div class="modal-body p-4">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-semibold">Código / Clave de la Materia</label>
                                                                        <input type="text" name="codigo" value="{{ $materia->codigo_materia }}" class="form-control rounded-3" maxlength="30" required>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label fw-semibold">Nombre de la Materia</label>
                                                                        <input type="text" name="nombre_materia" value="{{ $materia->nombre_materia }}" class="form-control rounded-3" maxlength="100" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-warning fw-semibold rounded-3">
                                                                        <i class="bi bi-check2-circle me-1"></i>Guardar Cambios
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border">Impartida por otro docente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                            Aún no has registrado materias para este grupo. Usa el formulario lateral para agregar la primera.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Alumnos Inscritos / Solicitudes en este Grupo con Regla Universitaria (3 Retardos = 1 Falta) y Derecho a Examen -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-people-fill me-2 text-primary"></i>Alumnos Inscritos, Solicitudes y Derecho a Examen Ordinario
                    </h5>
                    <p class="text-muted small mb-0">
                        Regla universitaria activa: <strong>cada 3 retardos acumulados en una misma asignatura equivalen a 1 falta</strong> (Límite para Derecho a Examen: máx. 2 faltas; con 3 faltas pasa a <strong>SD - Sin Derecho</strong>).
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if ($misMateriasGrupo->isNotEmpty())
                        <button type="button" class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalInscribirAlumnoGrupo">
                            <i class="bi bi-person-plus-fill me-1"></i>Inscribir Alumno en mi Asignatura
                        </button>
                    @endif
                    <span class="badge bg-secondary rounded-pill">{{ $grupo->matriculas->count() }} Registros</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Materia</th>
                            <th class="text-center">Faltas Acumuladas (3 Ret. = 1 Falta)</th>
                            <th class="text-center">Derecho a Examen</th>
                            <th class="text-center">Estado Inscripción</th>
                            <th class="text-end">Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($grupo->matriculas as $matricula)
                            @php
                                $claveFaltas    = $matricula->materia_id . '_' . $matricula->user_id;
                                $faltasAlumno   = $faltasPorAlumnoMateria[$claveFaltas] ?? 0;
                                $faltasDirectas = $faltasDirectasPorAlumnoMateria[$claveFaltas] ?? 0;
                                $retardosAcum   = $retardosPorAlumnoMateria[$claveFaltas] ?? 0;
                                $faltasPorRet   = intdiv($retardosAcum, 3);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $matricula->alumno->name ?? 'Alumno' }}</div>
                                    <div class="text-muted small">{{ $matricula->alumno->email ?? '-' }}</div>
                                </td>
                                <td>
                                    @if ($matricula->materia)
                                        <a href="{{ route('materias.show', $matricula->materia->id) }}" class="text-decoration-none text-dark">
                                            <span class="badge bg-secondary me-1">{{ $matricula->materia->codigo_materia }}</span>
                                            {{ $matricula->materia->nombre_materia }}
                                        </a>
                                    @else
                                        <span class="text-muted">Sin materia</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold {{ $faltasAlumno >= 3 ? 'text-danger' : ($faltasAlumno === 2 ? 'text-warning text-dark' : 'text-success') }}">
                                        {{ $faltasAlumno }} / 3 faltas
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Directas: {{ $faltasDirectas }} | Retardos: {{ $retardosAcum }} (+{{ $faltasPorRet }} falta{{ $faltasPorRet === 1 ? '' : 's' }})
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($faltasAlumno >= 3)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-octagon-fill me-1"></i>Sin Derecho a Examen (SD)
                                        </span>
                                    @elseif ($faltasAlumno === 2)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>En Riesgo / Preventivo (2/3)
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Con Derecho a Ordinario
                                        </span>
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
                                            <button type="submit" class="btn btn-sm btn-success rounded-3 me-1">
                                                <i class="bi bi-check-lg"></i> Aceptar
                                            </button>
                                        </form>
                                    @endif

                                    @if ($matricula->estado === 'pendiente')
                                        <form action="{{ route('profesor.solicitudes.actualizar', $matricula->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-3 me-1">
                                                <i class="bi bi-x-lg"></i> Rechazar
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('profesor.matriculas.destroy', $matricula->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas dar de baja a este alumno de la materia?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Dar de baja al alumno">
                                            <i class="bi bi-person-dash me-1"></i>Dar de Baja
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay alumnos inscritos ni solicitudes registradas en este grupo todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Inscribir Alumno Manualmente desde el Grupo (solo en las asignaturas del docente) -->
    @if ($misMateriasGrupo->isNotEmpty())
        <div class="modal fade" id="modalInscribirAlumnoGrupo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-plus-fill me-2"></i>Inscribir Alumno en {{ $grupo->nombre_grupo }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('profesor.matriculas.manual') }}" method="POST" id="formInscribirGrupo">
                        @csrf
                        <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">
                        <input type="hidden" name="user_id" id="inputUserIdGrupo" required>
                        <div class="modal-body p-4" id="buscadorAlumnosGrupoContainer" data-search-url="{{ route('profesor.alumnos.buscar') }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">1. Tu Asignatura en este Grupo</label>
                                <select name="materia_id" id="selectMateriaModalGrupo" class="form-select rounded-3" required>
                                    <option value="" selected disabled>-- Selecciona una asignatura que impartes --</option>
                                    @foreach ($misMateriasGrupo as $mat)
                                        <option value="{{ $mat->id }}">[{{ $mat->codigo_materia }}] {{ $mat->nombre_materia }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold">2. Buscar Alumno (Nombre, Matrícula o Correo)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                    <input type="text" id="inputBuscarAlumnoGrupo" class="form-control" autocomplete="off" placeholder="Escribe al menos 2 letras para buscar...">
                                </div>
                                <div class="form-text" id="estadoBusquedaGrupo">
                                    Ingresa al menos 2 caracteres para buscar en el padrón estudiantil.
                                </div>

                                <!-- Lista de coincidencias AJAX -->
                                <div id="resultadosAlumnosGrupo" class="list-group mt-2 shadow-sm d-none"></div>

                                <!-- Tarjeta de alumno seleccionado -->
                                <div id="alumnoSeleccionadoGrupo" class="alert alert-primary d-none align-items-center justify-content-between py-2 px-3 mt-2 mb-0 rounded-3">
                                    <div class="small">
                                        <div class="fw-bold" id="nombreAlumnoSelGrupo"></div>
                                        <div class="text-muted" id="detalleAlumnoSelGrupo"></div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btnLimpiarAlumnoGrupo" title="Cambiar alumno">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" id="btnSubmitInscribirGrupo" class="btn btn-primary fw-semibold rounded-3" disabled>
                                <i class="bi bi-check2-circle me-1"></i>Inscribir Alumno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div id="datos-charts-grupo"
         data-asistencias="{{ $statsGrupo['asistencias'] }}"
         data-retardos="{{ $statsGrupo['retardos'] }}"
         data-permisos="{{ $statsGrupo['permisos'] }}"
         data-faltas="{{ $statsGrupo['faltas'] }}"
         data-materias-labels="{{ json_encode($nombresMateriasChart) }}"
         data-materias-valores="{{ json_encode($porcentajesMateriasChart) }}"
         class="d-none"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-width]').forEach(function (el) {
                el.style.width = el.getAttribute('data-width') + '%';
            });
            const datosEl = document.getElementById('datos-charts-grupo');
            const canvasDona = document.getElementById('chartIncidenciasGrupo');
            if (canvasDona && datosEl) {
                new Chart(canvasDona, {
                    type: 'doughnut',
                    data: {
                        labels: ['Asistencias', 'Retardos', 'Permisos', 'Faltas'],
                        datasets: [{
                            data: [
                                Number(datosEl.dataset.asistencias),
                                Number(datosEl.dataset.retardos),
                                Number(datosEl.dataset.permisos),
                                Number(datosEl.dataset.faltas)
                            ],
                            backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } },
                        cutout: '62%'
                    }
                });
            }

            const canvasBarras = document.getElementById('chartMateriasGrupo');
            if (canvasBarras && datosEl) {
                const labels = JSON.parse(datosEl.dataset.materiasLabels || '[]');
                const valores = JSON.parse(datosEl.dataset.materiasValores || '[]');
                const colores = valores.map(v => v >= 85 ? '#198754' : (v >= 70 ? '#ffc107' : '#dc3545'));

                new Chart(canvasBarras, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '% de Asistencia Efectiva',
                            data: valores,
                            backgroundColor: colores,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: value => value + '%' }
                            }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // Buscador dinámico AJAX para Inscribir Alumno Manualmente en Grupo
            const buscadorContainer = document.getElementById('buscadorAlumnosGrupoContainer');
            if (buscadorContainer) {
                const searchUrl = buscadorContainer.getAttribute('data-search-url');
                const selectMateria = document.getElementById('selectMateriaModalGrupo');
                const inputBuscar = document.getElementById('inputBuscarAlumnoGrupo');
                const inputUserId = document.getElementById('inputUserIdGrupo');
                const resultadosEl = document.getElementById('resultadosAlumnosGrupo');
                const estadoEl = document.getElementById('estadoBusquedaGrupo');
                const seleccionadoEl = document.getElementById('alumnoSeleccionadoGrupo');
                const nombreSelEl = document.getElementById('nombreAlumnoSelGrupo');
                const detalleSelEl = document.getElementById('detalleAlumnoSelGrupo');
                const btnLimpiar = document.getElementById('btnLimpiarAlumnoGrupo');
                const btnSubmit = document.getElementById('btnSubmitInscribirGrupo');

                let debounceTimer = null;

                function actualizarBotonSubmit() {
                    btnSubmit.disabled = !(inputUserId.value && selectMateria.value);
                }

                function limpiarSeleccion() {
                    inputUserId.value = '';
                    seleccionadoEl.classList.add('d-none');
                    seleccionadoEl.classList.remove('d-flex');
                    inputBuscar.classList.remove('d-none');
                    inputBuscar.value = '';
                    inputBuscar.focus();
                    actualizarBotonSubmit();
                }

                btnLimpiar.addEventListener('click', limpiarSeleccion);
                selectMateria.addEventListener('change', function () {
                    if (inputUserId.value) {
                        limpiarSeleccion();
                    }
                    actualizarBotonSubmit();
                });

                inputBuscar.addEventListener('input', function () {
                    const q = this.value.trim();
                    clearTimeout(debounceTimer);

                    if (q.length < 2) {
                        resultadosEl.innerHTML = '';
                        resultadosEl.classList.add('d-none');
                        estadoEl.textContent = 'Ingresa al menos 2 caracteres para buscar en el padrón estudiantil.';
                        return;
                    }

                    estadoEl.textContent = 'Buscando estudiantes...';

                    debounceTimer = setTimeout(function () {
                        const matId = selectMateria.value || 0;
                        const url = searchUrl + '?q=' + encodeURIComponent(q) + '&materia_id=' + encodeURIComponent(matId);
                        fetch(url)
                            .then(function (r) { return r.json(); })
                            .then(function (lista) {
                                resultadosEl.innerHTML = '';

                                if (!lista || lista.length === 0) {
                                    resultadosEl.classList.add('d-none');
                                    estadoEl.textContent = 'No se encontraron alumnos con ese criterio.';
                                    return;
                                }

                                estadoEl.textContent = 'Mostrando ' + lista.length + ' coincidencia(s). Haz clic en el alumno:';
                                resultadosEl.classList.remove('d-none');

                                lista.forEach(function (al) {
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 small';

                                    const infoDiv = document.createElement('div');
                                    const nameStrong = document.createElement('div');
                                    nameStrong.className = 'fw-semibold text-dark';
                                    nameStrong.textContent = al.name;

                                    const subSpan = document.createElement('div');
                                    subSpan.className = 'text-muted';
                                    subSpan.textContent = 'Matrícula: ' + al.matricula_codigo + ' | ' + al.email;

                                    infoDiv.appendChild(nameStrong);
                                    infoDiv.appendChild(subSpan);
                                    btn.appendChild(infoDiv);

                                    if (al.ya_inscrito) {
                                        const badge = document.createElement('span');
                                        badge.className = 'badge bg-secondary ms-2';
                                        badge.textContent = 'Ya inscrito';
                                        btn.appendChild(badge);
                                        btn.disabled = true;
                                    } else {
                                        const badge = document.createElement('span');
                                        badge.className = 'badge bg-primary ms-2';
                                        badge.textContent = 'Elegir';
                                        btn.appendChild(badge);

                                        btn.addEventListener('click', function () {
                                            inputUserId.value = al.id;
                                            nombreSelEl.textContent = al.name;
                                            detalleSelEl.textContent = 'Matrícula: ' + al.matricula_codigo + ' (' + al.email + ')';
                                            seleccionadoEl.classList.remove('d-none');
                                            seleccionadoEl.classList.add('d-flex');
                                            resultadosEl.classList.add('d-none');
                                            inputBuscar.classList.add('d-none');
                                            estadoEl.textContent = 'Alumno seleccionado.';
                                            actualizarBotonSubmit();
                                        });
                                    }

                                    resultadosEl.appendChild(btn);
                                });
                            })
                            .catch(function () {
                                estadoEl.textContent = 'Error al consultar el buscador de alumnos.';
                            });
                    }, 260);
                });
            }
        });
    </script>
</body>
</html>
