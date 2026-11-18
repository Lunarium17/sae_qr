<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $materia->nombre_materia }} | Alumnos Inscritos - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        @include('partials.panel-css')
    <style>
        /* Acento institucional para la tarjeta principal de la materia */
        .materia-prota { border-top: 4px solid #2563EB; }

        /* Clave de la materia destacada (ej. BD-701) */
        .clave-materia {
            display: inline-flex; align-items: center; gap: .45rem;
            background: #EFF6FF; color: #1D4ED8;
            border: 1px solid #BFDBFE; border-radius: 50rem;
            padding: .45rem 1rem; font-weight: 700; font-size: .95rem;
        }
        .clave-materia i { font-size: 1rem; }

        /* Mini-tarjetas Bento de métricas */
        .metrica-mini {
            background: #FFFFFF;
            border: 1px solid rgba(203, 213, 225, .9);
            border-radius: 1rem;
            padding: .7rem 1.15rem;
            display: inline-flex; align-items: center; gap: .7rem;
            box-shadow: 0 2px 6px -2px rgba(15,23,42,.08);
            min-width: 132px;
        }
        .metrica-mini i { font-size: 1.3rem; flex-shrink: 0; }
        .metrica-mini .num { font-size: 1.45rem; font-weight: 800; line-height: 1.05; }
        .metrica-mini .lbl { font-size: .72rem; color: #64748B; font-weight: 600; }
        .metrica-mini-azul i { color: #2563EB; }
        .metrica-mini-azul .num { color: #1D4ED8; }
        .metrica-mini-ambar i { color: #D97706; }
        .metrica-mini-ambar .num { color: #B45309; }

        /* Separacion reforzada de las tarjetas inferiores */
        .panel-definido { box-shadow: 0 4px 12px -6px rgba(15,23,42,.12); }

        /* Bloque de incidencias limpio: contenedor neutro con puntos indicadores */
        .incidencias-box {
            display: inline-flex; flex-wrap: wrap; justify-content: center; gap: .5rem;
            background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: .875rem;
            padding: .45rem .75rem;
        }
        .inc-item {
            display: inline-flex; align-items: center; gap: .3rem;
            color: #94A3B8; font-size: .78rem; font-weight: 600; line-height: 1.4;
            white-space: nowrap;
        }
        .inc-item .dot { width: 7px; height: 7px; border-radius: 50%; background: #CBD5E1; opacity: .4; }
        .inc-item.inc-on { color: #334155; }
        .inc-item.inc-on .dot { opacity: 1; }
        .inc-item .val { font-weight: 700; }
        .inc-a .dot { background: #059669; }
        .inc-r .dot { background: #D97706; }
        .inc-p .dot { background: #2563EB; }
        .inc-f .dot { background: #DC2626; }
        .inc-a.inc-on .val { color: #047857; }
        .inc-r.inc-on .val { color: #B45309; }
        .inc-p.inc-on .val { color: #1D4ED8; }
        .inc-f.inc-on .val { color: #B91C1C; }

        /* Pildoras semanticas limpias (verde, ambar, rojo, azul, neutra) */
        .verde-limpio { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .ambar-limpio { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
        .rojo-limpio  { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
        .azul-suave   { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
        .neutra-slate { background: #F1F5F9; color: #334155; border: 1px solid #E2E8F0; }

        /* Aire en las filas del padron */
        .tabla-padron tbody td { padding-top: 1.05rem; padding-bottom: 1.05rem; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ $materia->grupo_id ? route('grupos.show', $materia->grupo_id) : route('grupos.index') }}">
                SAE QR | {{ $materia->nombre_materia }}
            </a>
            <div class="d-flex align-items-center gap-2">
                @if ($materia->grupo_id)
                    <a href="{{ route('grupos.show', $materia->grupo_id) }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Grupo {{ $materia->grupo->nombre_grupo }}
                    </a>
                @else
                    <a href="{{ route('grupos.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Grupos
                    </a>
                @endif
                <a href="{{ route('profesor.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    <i class="bi bi-house-door me-1"></i>Panel Principal
                </a>
                @include('partials.campana')
            </div>
        </div>
    </nav>

    <div class="container my-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session('error') }}
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
        <div class="card rounded-4 p-4 bg-white mb-4 materia-prota">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="clave-materia"><i class="bi bi-journal-bookmark-fill"></i>{{ $materia->codigo_materia }}</span>
                        <h3 class="fw-bold text-dark mb-0">{{ $materia->nombre_materia }}</h3>
                        <a href="{{ route('materias.edit', $materia->id) }}" class="btn btn-sm btn-outline-warning text-dark rounded-3 ms-1">
                            <i class="bi bi-pencil-square me-1"></i>Editar Materia
                        </a>
                    </div>
                    @if ($materia->grupo)
                        <p class="texto-slate mb-0 mt-2">
                            <span class="me-3"><strong>Grupo:</strong> {{ $materia->grupo->nombre_grupo }}</span>
                            @if ($materia->grupo->carrera)
                                <span class="me-3"><strong>Carrera:</strong> {{ $materia->grupo->carrera }}</span>
                            @endif
                            <span class="me-3"><strong>Semestre:</strong> {{ $materia->grupo->grado }}</span>
                            <span class="me-3"><strong>Turno:</strong> {{ $materia->grupo->turno }}</span>
                            <span><strong>Periodo:</strong> {{ preg_replace('/([A-Za-zÁÉÍÓÚáéíóú])(\d{4})/u', '$1 $2', $materia->grupo->periodo_lectivo) }}</span>
                        </p>
                    @endif
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="metrica-mini metrica-mini-azul">
                        <i class="bi bi-person-check-fill"></i>
                        <div>
                            <div class="num">{{ $materia->matriculas->where('estado', 'aceptado')->count() }}</div>
                            <div class="lbl">Alumnos Aceptados</div>
                        </div>
                    </div>
                    <div class="metrica-mini metrica-mini-ambar">
                        <i class="bi bi-hourglass-split"></i>
                        <div>
                            <div class="num">{{ $materia->matriculas->where('estado', 'pendiente')->count() }}</div>
                            <div class="lbl">Pendientes</div>
                        </div>
                    </div>
                    <div class="vr d-none d-sm-block opacity-25"></div>
                    <a href="{{ route('sesiones.index', ['materia_id' => $materia->id]) }}" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-qr-code-scan me-2"></i>Iniciar Clase QR
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- 2. Formulario Lateral: Inscribir Alumno Manualmente por Búsqueda Dinámica -->
            <div class="col-lg-4">
                <div class="card rounded-4 p-4 bg-white h-100 panel-definido">
                    <h5 class="fw-bold text-dark mb-3">
                        Inscribir Alumno Manualmente
                        <span class="info-icon" tabindex="0" role="button" aria-label="Busca por nombre, matrícula o correo para inscribir en {{ $materia->nombre_materia }}.">
                            <i class="bi bi-info-circle"></i>
                            <span class="info-icon-text" role="tooltip">Busca por nombre, matrícula o correo para inscribir en {{ $materia->nombre_materia }}.</span>
                        </span>
                    </h5>

                    <form action="{{ route('profesor.matriculas.manual') }}" method="POST" id="formInscribirMateria">
                        @csrf
                        <input type="hidden" name="materia_id" value="{{ $materia->id }}">
                        <input type="hidden" name="grupo_id" value="{{ $materia->grupo_id }}">
                        <input type="hidden" name="user_id" id="inputUserIdMateria" required>

                        <div class="mb-3" id="buscadorAlumnosContainer" data-search-url="{{ route('profesor.alumnos.buscar') }}" data-materia-id="{{ $materia->id }}">
                            <label class="form-label fw-semibold">Buscar Estudiante</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" id="inputBuscarAlumnoMateria" class="form-control" autocomplete="off" placeholder="Escribe al menos 2 letras...">
                            </div>
                            <div class="form-text" id="estadoBusquedaMateria">
                                Ingresa al menos 2 caracteres para buscar en el padrón estudiantil.
                            </div>

                            <!-- Resultados de búsqueda AJAX -->
                            <div id="resultadosAlumnosMateria" class="list-group mt-2 shadow-sm d-none"></div>

                            <!-- Tarjeta del alumno seleccionado -->
                            <div id="alumnoSeleccionadoMateria" class="alert alert-primary d-none align-items-center justify-content-between py-2 px-3 mt-2 mb-0 rounded-3">
                                <div class="small">
                                    <div class="fw-bold" id="nombreAlumnoSelMateria"></div>
                                    <div class="texto-slate" id="detalleAlumnoSelMateria"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" id="btnLimpiarAlumnoMateria" title="Cambiar alumno">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmitInscribirMateria" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" disabled>
                            <i class="bi bi-check2-circle me-2"></i>Inscribir en esta Materia
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. Tabla de Alumnos Inscritos y Resumen de Asistencia en esta Materia -->
            <div class="col-lg-8">
                <div class="card rounded-4 p-4 bg-white h-100 panel-definido">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">
                            Padrón de Alumnos de la Materia
                            <span class="info-icon" tabindex="0" role="button" aria-label="3 retardos = 1 falta. Con 2 faltas: en riesgo. Con 3: sin derecho a examen.">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">3 retardos = 1 falta. Con 2 faltas: en riesgo. Con 3: sin derecho a examen.</span>
                            </span>
                        </h5>
                        <span class="badge badge-estado badge-permiso rounded-pill">{{ $materia->matriculas->count() }} Registros</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-moderna table-hover tabla-padron align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Alumno</th>
                                    <th class="text-center">Incidencias</th>
                                    <th class="text-center">Derecho a Examen</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Gestión de Inscripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($materia->matriculas as $matricula)
                                    @php
                                        $regsAlumno     = $registrosMateria->get($matricula->user_id, collect());
                                        $asistCount     = $regsAlumno->where('estado_incidencia', 'asistencia')->count();
                                        $retCount       = $regsAlumno->where('estado_incidencia', 'retardo')->count();
                                        $permCount      = $regsAlumno->where('estado_incidencia', 'permiso')->count();
                                        $faltCount      = $regsAlumno->where('estado_incidencia', 'falta')->count();
                                        $faltasPorRet   = intdiv($retCount, 3);
                                        $faltasTotales  = $faltCount + $faltasPorRet;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $matricula->alumno->name ?? 'Alumno' }}</div>
                                            <div class="texto-slate small">{{ $matricula->alumno->email ?? '-' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="incidencias-box">
                                                <span class="inc-item inc-a {{ $asistCount > 0 ? 'inc-on' : '' }}" title="Asistencias"><span class="dot"></span>A: <strong class="val">{{ $asistCount }}</strong></span>
                                                <span class="inc-item inc-r {{ $retCount > 0 ? 'inc-on' : '' }}" title="Retardos acumulados"><span class="dot"></span>R: <strong class="val">{{ $retCount }}</strong></span>
                                                <span class="inc-item inc-p {{ $permCount > 0 ? 'inc-on' : '' }}" title="Permisos / Justificados"><span class="dot"></span>P: <strong class="val">{{ $permCount }}</strong></span>
                                                <span class="inc-item inc-f {{ $faltCount > 0 ? 'inc-on' : '' }}" title="Faltas directas"><span class="dot"></span>F: <strong class="val">{{ $faltCount }}</strong></span>
                                            </div>
                                            <div class="small texto-slate mt-2">
                                                Total computado:
                                                @if ($faltasTotales >= 3)
                                                    <strong class="fw-bold" style="color: #B91C1C;">{{ $faltasTotales }} / 3 faltas</strong>
                                                @elseif ($faltasTotales > 0)
                                                    <strong class="fw-bold" style="color: #B45309;">{{ $faltasTotales }} / 3 faltas</strong>
                                                @else
                                                    <strong>{{ $faltasTotales }} / 3 faltas</strong>
                                                @endif
                                                @if ($faltasPorRet > 0)
                                                    <span class="fw-semibold" style="color: #B45309;">({{ $faltCount }} dir. + {{ $faltasPorRet }} por retardos)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if ($faltasTotales >= 3)
                                                <span class="badge badge-estado rojo-limpio">
                                                    Sin Derecho a Examen (SD)
                                                </span>
                                            @elseif ($faltasTotales === 2)
                                                <span class="badge badge-estado ambar-limpio">
                                                    En Riesgo (2/3)
                                                </span>
                                            @else
                                                <span class="badge badge-estado verde-limpio">
                                                    Con Derecho a Ordinario
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($matricula->estado === 'aceptado')
                                                <span class="badge badge-estado neutra-slate">Aceptado</span>
                                            @elseif ($matricula->estado === 'pendiente')
                                                <span class="badge badge-estado ambar-limpio">Pendiente</span>
                                            @else
                                                <span class="badge badge-estado rojo-limpio">Rechazado</span>
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
                                        <td colspan="5" class="text-center py-5 texto-slate">
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('buscadorAlumnosContainer');
            if (!container) return;

            const searchUrl = container.getAttribute('data-search-url');
            const materiaId = container.getAttribute('data-materia-id');
            const inputBuscar = document.getElementById('inputBuscarAlumnoMateria');
            const inputUserId = document.getElementById('inputUserIdMateria');
            const resultadosEl = document.getElementById('resultadosAlumnosMateria');
            const estadoEl = document.getElementById('estadoBusquedaMateria');
            const seleccionadoEl = document.getElementById('alumnoSeleccionadoMateria');
            const nombreSelEl = document.getElementById('nombreAlumnoSelMateria');
            const detalleSelEl = document.getElementById('detalleAlumnoSelMateria');
            const btnLimpiar = document.getElementById('btnLimpiarAlumnoMateria');
            const btnSubmit = document.getElementById('btnSubmitInscribirMateria');

            let debounceTimer = null;

            function limpiarSeleccion() {
                inputUserId.value = '';
                seleccionadoEl.classList.add('d-none');
                seleccionadoEl.classList.remove('d-flex');
                inputBuscar.classList.remove('d-none');
                inputBuscar.value = '';
                inputBuscar.focus();
                btnSubmit.disabled = true;
            }

            btnLimpiar.addEventListener('click', limpiarSeleccion);

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
                    const url = searchUrl + '?q=' + encodeURIComponent(q) + '&materia_id=' + encodeURIComponent(materiaId);
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
                                subSpan.className = 'texto-slate';
                                subSpan.textContent = (al.matricula_codigo ? 'Matrícula: ' + al.matricula_codigo + ' | ' : '') + al.email;

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
                                        detalleSelEl.textContent = (al.matricula_codigo ? 'Matrícula: ' + al.matricula_codigo + ' — ' : '') + al.email;
                                        seleccionadoEl.classList.remove('d-none');
                                        seleccionadoEl.classList.add('d-flex');
                                        resultadosEl.classList.add('d-none');
                                        inputBuscar.classList.add('d-none');
                                        estadoEl.textContent = 'Alumno seleccionado listo para inscribir.';
                                        btnSubmit.disabled = false;
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

