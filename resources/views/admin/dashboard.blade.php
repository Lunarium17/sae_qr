<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    @include('partials.ui_helpers')

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-shield-lock-fill text-info me-2"></i>SAE QR | Panel de Administrador
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.perfil') }}" class="btn btn-light btn-sm fw-semibold rounded-3">
                    <i class="bi bi-person-gear me-1"></i>Mi Perfil
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <!-- 1. Bienvenida y Acciones Rápidas -->
        <div class="card sae-greeting-banner border-0 shadow-sm rounded-4 p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if (\Illuminate\Support\Facades\Auth::user()->foto_perfil)
                        <img src="{{ asset('storage/' . \Illuminate\Support\Facades\Auth::user()->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle object-fit-cover border border-2 border-white shadow-sm" style="width: 58px; height: 58px;">
                    @endif
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h3 class="fw-bold sae-greeting-title mb-0"><span class="js-saludo-horario">{{ $saludoHorarioSae ?? 'Bienvenido' }}</span>, {{ \Illuminate\Support\Facades\Auth::user()->name }}</h3>
                            <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Genera códigos de invitación de un solo uso para nuevos profesores, administra cuentas de usuarios y supervisa las estadísticas generales de la institución.">!</span>
                        </div>
                        <div class="sae-greeting-meta">
                            <i class="bi bi-calendar3 me-1"></i>{{ $fechaActualEspanolSae ?? '' }}
                            <span class="mx-2">&bull;</span>
                            <i class="bi bi-calendar2-week me-1"></i>{{ $periodoEscolarActualSae ?? '' }}
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('admin.codigos') }}" class="btn btn-light text-dark rounded-3 px-3 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-key-fill me-2 text-primary"></i>Gestionar Códigos de Acceso Docente
                    </a>
                    <button type="button" class="btn btn-success rounded-3 px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearGrupoAdmin">
                        <i class="bi bi-plus-circle-fill me-2"></i>Crear Grupo Oficial Universitario
                    </button>
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-info text-dark rounded-3 px-3 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-people-fill me-2"></i>Directorio y Gestión Usuarios
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Tarjetas de Indicadores Globales (KPIs) -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 me-3">
                            <i class="bi bi-mortarboard-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Total Alumnos</div>
                            <h3 class="fw-bold mb-0">{{ $totalAlumnos }}</h3>
                            <small class="text-muted">Cuentas estudiantiles</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 me-3">
                            <i class="bi bi-person-workspace fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Total Profesores</div>
                            <h3 class="fw-bold mb-0">{{ $totalProfesores }}</h3>
                            <small class="text-muted">Docentes activos</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3 me-3">
                            <i class="bi bi-key-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Códigos Docentes</div>
                            <h3 class="fw-bold mb-0">{{ $codigosDisponiblesCount }} <span class="fs-6 fw-normal text-muted">disp.</span></h3>
                            <small class="text-muted">{{ $codigosUsadosCount }} canjeados</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 me-3">
                            <i class="bi bi-collection-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-semibold">Grupos y Materias</div>
                            <h3 class="fw-bold mb-0">{{ $totalGrupos }} / {{ $totalMaterias }}</h3>
                            <small class="text-muted">{{ $totalSesiones }} sesiones impartidas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Sección Inferior: Vista Rápida de Códigos Docentes y Supervisión de Grupos -->
        <div class="row g-4">
            <!-- Columna Izquierda: Vista de Códigos Docentes Recientes -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="fw-bold mb-0"><i class="bi bi-key-fill text-primary me-2"></i>Códigos de Acceso Docente</h5>
                            <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Vista rápida de los códigos de invitación docente generados.">!</span>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nota / Destinatario</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($codigosRecientes as $cod)
                                        <tr>
                                            <td>
                                                <span class="fw-bold font-monospace text-primary">{{ $cod->codigo }}</span>
                                            </td>
                                            <td>
                                                @if ($cod->usado && $cod->usadoPor)
                                                    <span class="text-dark fw-semibold">{{ $cod->usadoPor->name }}</span>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $cod->usadoPor->email }}</div>
                                                @else
                                                    <span class="text-muted">{{ $cod->descripcion_nota ?: 'Sin nota asignada' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($cod->usado)
                                                    <span class="badge bg-secondary">Canjeado</span>
                                                @else
                                                    <span class="badge bg-success">Disponible</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if (!$cod->usado)
                                                    <button type="button" class="btn btn-primary btn-sm py-0 px-2 js-copiar-codigo" data-codigo="{{ $cod->codigo }}" title="Copiar código">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                Aún no has generado códigos de invitación docente.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Creación y Gestión de Grupos Oficiales Universitarios -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center">
                            <h5 class="fw-bold mb-0"><i class="bi bi-building text-primary me-2"></i>Catálogo de Grupos Oficiales</h5>
                            <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Crea y administra los grupos oficiales por Carrera y Semestre.">!</span>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Grupo / Carrera</th>
                                        <th>Semestre / Turno</th>
                                        <th>Materias Vinculadas</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($grupos as $grupo)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-primary">{{ $grupo->nombre_grupo }}</div>
                                                <div class="text-muted" style="font-size: 0.78rem;">
                                                    <i class="bi bi-mortarboard me-1"></i>{{ $grupo->carrera ?: 'Carrera general' }}
                                                </div>
                                                <div class="small mt-1">
                                                    @if ($grupo->profesor)
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">
                                                            <i class="bi bi-person-workspace me-1"></i>{{ $grupo->profesor->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted fst-italic" style="font-size: 0.74rem;">Sin docente asignado</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $grupo->grado }}</div>
                                                <span class="badge bg-light text-dark border">{{ $grupo->turno }}</span>
                                                <div class="text-muted" style="font-size: 0.72rem;">{{ $grupo->periodo_lectivo }}</div>
                                            </td>
                                            <td>
                                                @forelse ($grupo->materias as $mat)
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle me-1 mb-1">
                                                        {{ $mat->nombre_materia }} ({{ $mat->codigo_materia }})
                                                    </span>
                                                @empty
                                                    <span class="text-muted fst-italic">Sin materias registradas</span>
                                                @endforelse
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <button
                                                        type="button"
                                                        class="btn btn-warning text-dark btn-sm py-0 px-2 js-btn-editar-grupo-admin"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarGrupoAdmin"
                                                        data-action="{{ route('admin.grupos.update', $grupo->id) }}"
                                                        data-nombre="{{ $grupo->nombre_grupo }}"
                                                        data-grado="{{ $grupo->grado }}"
                                                        data-carrera="{{ $grupo->carrera }}"
                                                        data-profesor="{{ $grupo->profesor_id ?? '' }}"
                                                        data-turno="{{ $grupo->turno }}"
                                                        data-periodo="{{ $grupo->periodo_lectivo }}"
                                                        title="Editar Grupo Oficial"
                                                    >
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <form action="{{ route('admin.grupos.destroy', $grupo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este grupo oficial?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm py-0 px-2" title="Eliminar Grupo Oficial">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                No hay grupos oficiales registrados todavía. Haz clic en <strong>"Crear Grupo Oficial Universitario"</strong> arriba para crear el primero.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($grupos->hasPages())
                            <div class="pt-3 mt-3 border-top d-flex justify-content-center">
                                {{ $grupos->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Único Reutilizable: Editar Grupo Oficial -->
        <div class="modal fade text-start" id="modalEditarGrupoAdmin" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-warning text-dark border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>Editar Grupo Oficial: <span id="tituloGrupoEditarAdmin"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="formEditarGrupoAdmin" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-sm-5">
                                    <label class="form-label fw-semibold">Clave del Grupo <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre_grupo" id="editGrupoNombreAdmin" class="form-control rounded-3" maxlength="50" required>
                                </div>
                                <div class="col-sm-7">
                                    <label class="form-label fw-semibold">Semestre <span class="text-danger">*</span></label>
                                    <select name="grado" id="editGrupoGradoAdmin" class="form-select rounded-3" required>
                                        @for ($i = 1; $i <= 12; $i++)
                                            @php $sem = $i . '° Semestre'; @endphp
                                            <option value="{{ $sem }}">{{ $sem }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Carrera / Programa Educativo <span class="text-danger">*</span></label>
                                    <input type="text" name="carrera" id="editGrupoCarreraAdmin" class="form-control rounded-3" maxlength="120" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Profesor Asignado <span class="text-danger">*</span></label>
                                    <select name="profesor_id" id="editGrupoProfesorAdmin" class="form-select rounded-3" required>
                                        <option value="" disabled>Selecciona al profesor</option>
                                        @foreach ($profesores as $prof)
                                            <option value="{{ $prof->id }}">
                                                {{ $prof->name }} ({{ $prof->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Turno / Modalidad <span class="text-danger">*</span></label>
                                    <select name="turno" id="editGrupoTurnoAdmin" class="form-select rounded-3" required>
                                        <option value="Matutino">Matutino</option>
                                        <option value="Vespertino">Vespertino</option>
                                        <option value="Nocturno">Nocturno</option>
                                        <option value="Mixto / Sabatino">Mixto / Sabatino</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Periodo Semestral <span class="text-danger">*</span></label>
                                    <input type="text" name="periodo_lectivo" id="editGrupoPeriodoAdmin" class="form-control rounded-3" maxlength="50" required>
                                </div>
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

        <!-- Modal Crear Nuevo Grupo Oficial desde Administrador -->
        <div class="modal fade" id="modalCrearGrupoAdmin" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-building-add me-2"></i>Crear Grupo Oficial Universitario
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form action="{{ route('admin.grupos.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-sm-5">
                                    <label class="form-label fw-semibold">Clave del Grupo <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre_grupo" class="form-control rounded-3" maxlength="50" required>
                                </div>
                                <div class="col-sm-7">
                                    <label class="form-label fw-semibold">Semestre <span class="text-danger">*</span></label>
                                    <select name="grado" class="form-select rounded-3" required>
                                        <option value="" selected disabled>Selecciona el semestre</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}° Semestre">{{ $i }}° Semestre</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Carrera / Programa Educativo <span class="text-danger">*</span></label>
                                    <input type="text" name="carrera" class="form-control rounded-3" maxlength="120" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Profesor Asignado <span class="text-danger">*</span></label>
                                    <select name="profesor_id" class="form-select rounded-3" required>
                                        <option value="" selected disabled>Selecciona al profesor</option>
                                        @foreach ($profesores as $prof)
                                            <option value="{{ $prof->id }}">
                                                {{ $prof->name }} ({{ $prof->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Turno / Modalidad <span class="text-danger">*</span></label>
                                    <select name="turno" class="form-select rounded-3" required>
                                        <option value="Matutino">Matutino</option>
                                        <option value="Vespertino">Vespertino</option>
                                        <option value="Nocturno">Nocturno</option>
                                        <option value="Mixto / Sabatino">Mixto / Sabatino</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Periodo Semestral <span class="text-danger">*</span></label>
                                    <input type="text" name="periodo_lectivo" class="form-control rounded-3" maxlength="50" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary fw-semibold rounded-3">
                                <i class="bi bi-save me-1"></i>Guardar Grupo Oficial
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-barra-progreso').forEach(function (barra) {
                const pct = barra.getAttribute('data-porcentaje') || 0;
                barra.style.width = pct + '%';
            });

            document.querySelectorAll('.js-copiar-codigo').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const codigo = btn.getAttribute('data-codigo');
                    if (navigator.clipboard && codigo) {
                        navigator.clipboard.writeText(codigo).then(function () {
                            const iconoOriginal = btn.innerHTML;
                            btn.innerHTML = '<i class="bi bi-check2 text-white"></i>';
                            if (typeof window.mostrarNotificacionSae === 'function') {
                                window.mostrarNotificacionSae('success', 'Código ' + codigo + ' copiado al portapapeles.', 'Copiado');
                            }
                            setTimeout(function () {
                                btn.innerHTML = iconoOriginal;
                            }, 1500);
                        });
                    }
                });
            });

            const formEditarGrupo = document.getElementById('formEditarGrupoAdmin');
            const tituloGrupoEditar = document.getElementById('tituloGrupoEditarAdmin');
            const editGrupoNombre = document.getElementById('editGrupoNombreAdmin');
            const editGrupoGrado = document.getElementById('editGrupoGradoAdmin');
            const editGrupoCarrera = document.getElementById('editGrupoCarreraAdmin');
            const editGrupoProfesor = document.getElementById('editGrupoProfesorAdmin');
            const editGrupoTurno = document.getElementById('editGrupoTurnoAdmin');
            const editGrupoPeriodo = document.getElementById('editGrupoPeriodoAdmin');

            document.querySelectorAll('.js-btn-editar-grupo-admin').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!formEditarGrupo) return;
                    formEditarGrupo.setAttribute('action', btn.getAttribute('data-action'));
                    if (tituloGrupoEditar) tituloGrupoEditar.textContent = btn.getAttribute('data-nombre') || '';
                    if (editGrupoNombre) editGrupoNombre.value = btn.getAttribute('data-nombre') || '';
                    if (editGrupoGrado) editGrupoGrado.value = btn.getAttribute('data-grado') || '1° Semestre';
                    if (editGrupoCarrera) editGrupoCarrera.value = btn.getAttribute('data-carrera') || '';
                    if (editGrupoProfesor) editGrupoProfesor.value = btn.getAttribute('data-profesor') || '';
                    if (editGrupoTurno) editGrupoTurno.value = btn.getAttribute('data-turno') || 'Matutino';
                    if (editGrupoPeriodo) editGrupoPeriodo.value = btn.getAttribute('data-periodo') || '';
                });
            });
        });
    </script>
</body>
</html>

