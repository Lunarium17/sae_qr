<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción por Asignatura y Grupo - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-person-badge me-2"></i>SAE QR | Inscripción Modular por Asignatura</a>
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
            <!-- Formulario para solicitar inscripción independiente por asignatura y grupo (Carga AJAX) -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-1">Solicitar Inscripción en Asignatura</h5>
                    <p class="text-muted small mb-4">
                        Selecciona el grupo y busca la asignatura que deseas cursar. Las asignaturas se cargan dinámicamente para mantener la página rápida.
                    </p>

                    <form action="{{ route('alumno.solicitar.guardar') }}" method="POST" id="formSolicitarAsignatura" data-base-url="{{ url('/alumno/grupos') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">1. Selecciona el Grupo Académico</label>
                            <select id="select-grupo" name="grupo_id" class="form-select rounded-3" required>
                                <option value="" selected disabled>Elige un grupo académico...</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">
                                        {{ $grupo->nombre_grupo }}{{ $grupo->carrera ? ' — ' . $grupo->carrera : '' }} ({{ $grupo->grado }} | {{ $grupo->turno }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="bloque-buscador-materia">
                            <label class="form-label fw-semibold small text-muted mb-1">Filtrar asignatura por nombre o clave (opcional)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" id="input-filtrar-materia" class="form-control" autocomplete="off" placeholder="Ej. Cálculo, ING-301...">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">2. Selecciona la Asignatura</label>
                            <select id="select-materia" name="materia_id" class="form-select rounded-3" required disabled>
                                <option id="placeholder-materia" value="" selected disabled>Primero selecciona un grupo...</option>
                            </select>
                            <div id="ayuda-materia" class="form-text text-danger d-none mt-1">
                                No se encontraron asignaturas disponibles en este grupo.
                            </div>
                        </div>

                        <button id="btn-enviar" type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" disabled>
                            <i class="bi bi-send-fill me-2"></i>Enviar Solicitud de Asignatura
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historial de Solicitudes y Materias Inscritas individualmente -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-1">Mis Matrículas y Solicitudes por Asignatura</h5>
                    <p class="text-muted small mb-3">Carga académica individual asociada a cada grupo y materia.</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Grupo</th>
                                    <th>Asignatura</th>
                                    <th>Docente Titular</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($misMatriculas->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Aún no has enviado solicitudes de inscripción a asignaturas.</td>
                                    </tr>
                                @else
                                    @foreach($misMatriculas as $mat)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                                    {{ $mat->grupo->nombre_grupo ?? 'N/A' }} ({{ $mat->grupo->turno ?? '' }})
                                                </span>
                                                @if (!empty($mat->grupo->carrera))
                                                    <div class="small text-muted mt-1">{{ $mat->grupo->carrera }} — {{ $mat->grupo->grado }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $mat->materia->nombre_materia ?? 'N/A' }}</div>
                                                <small class="text-muted font-monospace">{{ $mat->materia->codigo_materia ?? '' }}</small>
                                            </td>
                                            <td class="small text-muted">
                                                <i class="bi bi-person-workspace me-1"></i>{{ optional(optional($mat->materia)->profesor)->name ?? 'Docente por asignar' }}
                                            </td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formSolicitarAsignatura');
            const baseUrl = form.getAttribute('data-base-url');
            const selectGrupo = document.getElementById('select-grupo');
            const selectMateria = document.getElementById('select-materia');
            const bloqueBuscador = document.getElementById('bloque-buscador-materia');
            const inputFiltrar = document.getElementById('input-filtrar-materia');
            const ayudaMateria = document.getElementById('ayuda-materia');
            const btnEnviar = document.getElementById('btn-enviar');

            let debounceTimer = null;

            function cargarMaterias(grupoId, filtroTexto) {
                if (!grupoId) return;

                selectMateria.disabled = true;
                btnEnviar.disabled = true;
                selectMateria.innerHTML = '<option value="" selected disabled>Cargando asignaturas...</option>';
                ayudaMateria.classList.add('d-none');

                const url = baseUrl + '/' + encodeURIComponent(grupoId) + '/materias?q=' + encodeURIComponent(filtroTexto || '');

                fetch(url)
                    .then(function (r) { return r.json(); })
                    .then(function (materias) {
                        selectMateria.innerHTML = '';

                        if (!materias || materias.length === 0) {
                            const optEmpty = document.createElement('option');
                            optEmpty.value = '';
                            optEmpty.disabled = true;
                            optEmpty.selected = true;
                            optEmpty.textContent = 'Sin asignaturas disponibles';
                            selectMateria.appendChild(optEmpty);
                            selectMateria.disabled = true;
                            ayudaMateria.classList.remove('d-none');
                            return;
                        }

                        const optDefault = document.createElement('option');
                        optDefault.value = '';
                        optDefault.disabled = true;
                        optDefault.selected = true;
                        optDefault.textContent = 'Elige una asignatura de este grupo (' + materias.length + ')...';
                        selectMateria.appendChild(optDefault);

                        materias.forEach(function (m) {
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            let texto = '[' + m.codigo_materia + '] ' + m.nombre_materia + ' — Prof. ' + m.profesor_nombre;
                            if (m.estado_matricula) {
                                texto += ' (Ya solicitada: ' + m.estado_matricula.toUpperCase() + ')';
                                opt.disabled = true;
                            }
                            opt.textContent = texto;
                            selectMateria.appendChild(opt);
                        });

                        selectMateria.disabled = false;
                    })
                    .catch(function () {
                        selectMateria.innerHTML = '<option value="" selected disabled>Error al cargar asignaturas</option>';
                    });
            }

            selectGrupo.addEventListener('change', function () {
                const grupoId = this.value;
                inputFiltrar.value = '';
                bloqueBuscador.classList.remove('d-none');
                cargarMaterias(grupoId, '');
            });

            inputFiltrar.addEventListener('input', function () {
                const grupoId = selectGrupo.value;
                const q = this.value.trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    cargarMaterias(grupoId, q);
                }, 250);
            });

            selectMateria.addEventListener('change', function () {
                btnEnviar.disabled = !this.value;
            });
        });
    </script>
</body>
</html>
