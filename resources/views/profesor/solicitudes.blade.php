<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes e Inscripciones de Alumnos - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}">
                <i class="bi bi-person-lines-fill me-2"></i>SAE QR | Solicitudes e Inscripciones
            </a>
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

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">Validación y Gestión de Alumnos en mis Asignaturas</h2>
                <p class="text-muted mt-1 mb-0">Aprueba, rechaza, busca o inscribe estudiantes en las asignaturas y grupos que impartes.</p>
            </div>
            @if ($misMaterias->isNotEmpty())
                <button type="button" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalInscribirDesdeSolicitudes">
                    <i class="bi bi-person-plus-fill me-2"></i>Inscribir Alumno por Búsqueda
                </button>
            @endif
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-4">
                <form action="{{ route('profesor.solicitudes') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="buscar" class="form-label fw-semibold small">Buscar Alumno (Nombre, Matrícula o Correo)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="buscar" id="buscar" value="{{ $buscar }}" class="form-control" placeholder="Ej. nombre, matrícula o correo...">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="materia_id" class="form-label fw-semibold small">Filtrar por Asignatura y Grupo</label>
                        <select name="materia_id" id="materia_id" class="form-select rounded-3">
                            <option value="0">Todas mis asignaturas</option>
                            @foreach ($misMaterias as $mat)
                                <option value="{{ $mat->id }}" {{ (int) $materiaFiltro === (int) $mat->id ? 'selected' : '' }}>
                                    [{{ $mat->codigo_materia }}] {{ $mat->nombre_materia }} — {{ $mat->grupo->nombre_grupo ?? 'Sin grupo' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="estado" class="form-label fw-semibold small">Estado</label>
                        <select name="estado" id="estado" class="form-select rounded-3">
                            <option value="todos" {{ $estadoFiltro === 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="pendiente" {{ $estadoFiltro === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                            <option value="aceptado" {{ $estadoFiltro === 'aceptado' ? 'selected' : '' }}>Aceptados</option>
                            <option value="rechazado" {{ $estadoFiltro === 'rechazado' ? 'selected' : '' }}>Rechazados</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-3">
                            <i class="bi bi-funnel-fill me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('profesor.solicitudes') }}" class="btn btn-outline-secondary rounded-3" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Listado de Solicitudes e Inscripciones</h5>
                <span class="badge bg-secondary rounded-pill">{{ $solicitudes->total() }} registro(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Alumno / Matrícula</th>
                            <th>Correo</th>
                            <th>Grupo Solicitado</th>
                            <th>Asignatura</th>
                            <th class="text-center">Estado Actual</th>
                            <th class="text-center pe-4">Decisión del Profesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($solicitudes->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2 opacity-50"></i>
                                    No se encontraron solicitudes o inscripciones con los filtros seleccionados.
                                </td>
                            </tr>
                        @else
                            @foreach($solicitudes as $sol)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $sol->alumno->name ?? 'Alumno' }}</div>
                                        <small class="text-muted font-monospace">{{ $sol->alumno->matricula_codigo ?? 'Sin matrícula' }}</small>
                                    </td>
                                    <td class="text-muted">{{ $sol->alumno->email ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('grupos.show', $sol->grupo_id) }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary text-decoration-none">
                                            {{ $sol->grupo->nombre_grupo ?? 'Grupo' }} ({{ $sol->grupo->turno ?? '' }})
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('materias.show', $sol->materia_id) }}" class="text-dark fw-semibold text-decoration-none">
                                            {{ $sol->materia->nombre_materia ?? 'Asignatura' }}
                                        </a>
                                    </td>
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
                                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark rounded-3 me-1" {{ $sol->estado === 'rechazado' ? 'disabled' : '' }}>
                                                <i class="bi bi-x-lg me-1"></i>Rechazar
                                            </button>
                                        </form>

                                        <!-- Botón Dar de Baja -->
                                        <form action="{{ route('profesor.matriculas.destroy', $sol->id) }}" method="POST" class="d-inline js-form-baja-solicitud">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Dar de baja (eliminar inscripción)">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($solicitudes->hasPages())
                <div class="card-footer bg-white border-0 py-3 px-4">
                    {{ $solicitudes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Inscribir Alumno Manualmente por Búsqueda Dinámica -->
    @if ($misMaterias->isNotEmpty())
        <div class="modal fade" id="modalInscribirDesdeSolicitudes" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-plus-fill me-2"></i>Inscribir Alumno por Búsqueda
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form action="{{ route('profesor.matriculas.manual') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" id="inputUserIdSol" required>
                        <div class="modal-body p-4" id="buscadorAlumnosSolContainer" data-search-url="{{ route('profesor.alumnos.buscar') }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">1. Selecciona tu Asignatura y Grupo</label>
                                <select name="materia_id" id="selectMateriaModalSol" class="form-select rounded-3" required>
                                    <option value="" selected disabled>-- Elige una asignatura que impartes --</option>
                                    @foreach ($misMaterias as $mat)
                                        <option value="{{ $mat->id }}">
                                            [{{ $mat->codigo_materia }}] {{ $mat->nombre_materia }} — {{ $mat->grupo->nombre_grupo ?? 'Sin grupo' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold">2. Buscar Alumno (Nombre, Matrícula o Correo)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                    <input type="text" id="inputBuscarAlumnoSol" class="form-control" autocomplete="off" placeholder="Escribe al menos 2 letras para buscar...">
                                </div>
                                <div class="form-text" id="estadoBusquedaSol">
                                    Ingresa al menos 2 caracteres para buscar en el padrón estudiantil.
                                </div>

                                <div id="resultadosAlumnosSol" class="list-group mt-2 shadow-sm d-none"></div>

                                <div id="alumnoSeleccionadoSol" class="alert alert-primary d-none align-items-center justify-content-between py-2 px-3 mt-2 mb-0 rounded-3">
                                    <div class="small">
                                        <div class="fw-bold" id="nombreAlumnoSelSol"></div>
                                        <div class="text-muted" id="detalleAlumnoSelSol"></div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btnLimpiarAlumnoSol" title="Cambiar alumno">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" id="btnSubmitInscribirSol" class="btn btn-primary fw-semibold rounded-3" disabled>
                                <i class="bi bi-check2-circle me-1"></i>Inscribir Alumno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-form-baja-solicitud').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!confirm('¿Seguro que deseas dar de baja a este alumno de la asignatura?')) {
                        e.preventDefault();
                    }
                });
            });

            const container = document.getElementById('buscadorAlumnosSolContainer');
            if (!container) return;

            const searchUrl = container.getAttribute('data-search-url');
            const selectMateria = document.getElementById('selectMateriaModalSol');
            const inputBuscar = document.getElementById('inputBuscarAlumnoSol');
            const inputUserId = document.getElementById('inputUserIdSol');
            const resultadosEl = document.getElementById('resultadosAlumnosSol');
            const estadoEl = document.getElementById('estadoBusquedaSol');
            const seleccionadoEl = document.getElementById('alumnoSeleccionadoSol');
            const nombreSelEl = document.getElementById('nombreAlumnoSelSol');
            const detalleSelEl = document.getElementById('detalleAlumnoSelSol');
            const btnLimpiar = document.getElementById('btnLimpiarAlumnoSol');
            const btnSubmit = document.getElementById('btnSubmitInscribirSol');

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
        });
    </script>
</body>
</html>
