<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .info-icon { position: relative; display: inline-flex; align-items: center; color: #0d6efd; cursor: help; vertical-align: middle; font-size: .8em; margin-left: .15rem; padding: .1rem; }
        .info-icon .info-icon-text { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); min-width: 240px; max-width: 320px; background: #212529; color: #fff; font-size: .75rem; font-weight: 400; line-height: 1.35; padding: .5rem .65rem; border-radius: .4rem; box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .2); opacity: 0; visibility: hidden; transition: opacity .15s; z-index: 10; text-align: left; }
        .info-icon:hover .info-icon-text, .info-icon:focus .info-icon-text, .info-icon:focus-within .info-icon-text { opacity: 1; visibility: visible; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
                SAE QR | Panel de Administrador
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.codigos') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                    Códigos Docentes
                </a>
                <a href="{{ route('admin.usuarios') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                    Usuarios
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <!-- 1. Bienvenida y Acciones Rápidas -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center mb-4">
            <h3 class="fw-bold text-dark mb-2">
                Bienvenido, {{ \Illuminate\Support\Facades\Auth::user()->name }}
            </h3>
            <p class="text-muted small mb-3">
                {{ \Illuminate\Support\Facades\Auth::user()->email }}
                | <span class="badge bg-info text-dark">Administrador</span>
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('admin.usuarios', ['rol' => 'profesor']) }}" class="btn btn-outline-secondary rounded-3 px-4 py-2">
                    <i class="bi bi-person-workspace me-2"></i>Ver Profesores ({{ $totalProfesores }})
                </a>
                <a href="{{ route('admin.usuarios', ['rol' => 'alumno']) }}" class="btn btn-outline-secondary rounded-3 px-4 py-2">
                    <i class="bi bi-mortarboard-fill me-2"></i>Ver Alumnos ({{ $totalAlumnos }})
                </a>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Barra de Asistencia Global Institucional -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">
                            Índice Global de Asistencia Escolar
                            <span class="info-icon" tabindex="0" role="button" aria-label="Promedio de asistencia institucional ({{ $totalRegistros }} pases de lista).">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">Promedio de asistencia institucional ({{ $totalRegistros }} pases de lista).</span>
                            </span>
                        </h6>
                    </div>
                    <span class="badge bg-{{ $colorSemaforoGlobal }} fs-6 px-3 py-2 rounded-pill">
                        {{ $porcentajeAsistencia }}% Asistencia Efectiva
                    </span>
                </div>

                <div class="progress rounded-pill mb-3" style="height: 12px;">
                    <div class="progress-bar bg-{{ $colorSemaforoGlobal }} js-barra-progreso" role="progressbar" data-porcentaje="{{ $porcentajeAsistencia }}" aria-valuenow="{{ $porcentajeAsistencia }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="row text-center g-2 small">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success fw-semibold">
                            Asistencias: {{ $asistenciasCount }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-dark fw-semibold">
                            Retardos: {{ $retardosCount }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-info bg-opacity-10 text-dark fw-semibold">
                            Permisos / Justif.: {{ $permisosCount }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger fw-semibold">
                            Faltas: {{ $faltasCount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Sección Inferior: Generador Rápido de Códigos Docentes y Supervisión de Grupos -->
        <div class="row g-4">
            <!-- Columna Izquierda: Generador Rápido de Códigos Docentes -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">
                                Códigos de Acceso Docente
                            <span class="info-icon" tabindex="0" role="button" aria-label="Códigos de un solo uso para registrar profesores.">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">Códigos de un solo uso para registrar profesores.</span>
                            </span>
                            </h5>
                        </div>
                        <a href="{{ route('admin.codigos') }}" class="btn btn-sm btn-outline-primary rounded-3">Ver todos</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form action="{{ route('admin.codigos.generar') }}" method="POST" class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                            @csrf
                            <div class="col-sm-8">
                                <label for="descripcion_nota" class="form-label small fw-semibold mb-1">Nota o Nombre del Profesor</label>
                                <input type="text" name="descripcion_nota" id="descripcion_nota" class="form-control form-control-sm rounded-3" maxlength="150">
                            </div>
                            <div class="col-sm-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3 py-2 fw-semibold">
                                    <i class="bi bi-plus-circle me-1"></i>Generar Código
                                </button>
                            </div>
                        </form>

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
                                                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 js-copiar-codigo" data-codigo="{{ $cod->codigo }}" title="Copiar código">
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
                        <div>
                            <h5 class="fw-bold mb-0">
                                Catálogo de Grupos Oficiales
                            <span class="info-icon" tabindex="0" role="button" aria-label="Grupos oficiales por carrera y semestre.">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">Grupos oficiales por carrera y semestre.</span>
                            </span>
                            </h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCrearGrupoAdmin">
                            <i class="bi bi-plus-circle me-1"></i>Nuevo Grupo Oficial
                        </button>
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
                                                <div class="text-muted" style="font-size: 0.78rem;">{{ $grupo->carrera ?: 'Carrera general' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $grupo->grado }}</div>
                                                <span class="badge bg-light text-dark border">{{ $grupo->turno }}</span>
                                                <div class="text-muted" style="font-size: 0.72rem;">{{ preg_replace('/([A-Za-zÁÉÍÓÚáéíóú])(\d{4})/u', '$1 $2', $grupo->periodo_lectivo) }}</div>
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
                                                    <button type="button" class="btn btn-outline-warning text-dark btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalEditarGrupoAdmin-{{ $grupo->id }}" title="Editar Grupo Oficial">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <form action="{{ route('admin.grupos.destroy', $grupo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este grupo oficial?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="Eliminar Grupo Oficial">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Modal Editar Grupo Oficial -->
                                                <div class="modal fade text-start" id="modalEditarGrupoAdmin-{{ $grupo->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0 shadow">
                                                            <div class="modal-header bg-warning text-dark border-0">
                                                                <h5 class="modal-title fw-bold">
                                                                    Editar Grupo Oficial: {{ $grupo->nombre_grupo }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                            </div>
                                                            <form action="{{ route('admin.grupos.update', $grupo->id) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-body p-4">
                                                                    <div class="row g-3">
                                                                        <div class="col-sm-5">
                                                                            <label class="form-label fw-semibold">Clave del Grupo <span class="text-danger">*</span></label>
                                                                            <input type="text" name="nombre_grupo" class="form-control rounded-3" value="{{ $grupo->nombre_grupo }}" maxlength="50" required>
                                                                        </div>
                                                                        <div class="col-sm-7">
                                                                            <label class="form-label fw-semibold">Semestre <span class="text-danger">*</span></label>
                                                                            <select name="grado" class="form-select rounded-3" required>
                                                                                @for ($i = 1; $i <= 12; $i++)
                                                                                    @php $sem = $i . '° Semestre'; @endphp
                                                                                    <option value="{{ $sem }}" {{ $grupo->grado === $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                                                                @endfor
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <label class="form-label fw-semibold">Carrera / Programa Educativo <span class="text-danger">*</span></label>
                                                                            <input type="text" name="carrera" class="form-control rounded-3" list="listaCarrerasAdmin" value="{{ $grupo->carrera }}" maxlength="120" required>
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <label class="form-label fw-semibold">Turno / Modalidad <span class="text-danger">*</span></label>
                                                                            <select name="turno" class="form-select rounded-3" required>
                                                                                <option value="Matutino" {{ $grupo->turno === 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                                                                <option value="Vespertino" {{ $grupo->turno === 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                                                                <option value="Nocturno" {{ $grupo->turno === 'Nocturno' ? 'selected' : '' }}>Nocturno</option>
                                                                                <option value="Mixto / Sabatino" {{ $grupo->turno === 'Mixto / Sabatino' ? 'selected' : '' }}>Mixto / Sabatino</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <label class="form-label fw-semibold">Periodo Semestral <span class="text-danger">*</span></label>
                                                                            <input type="text" name="periodo_lectivo" class="form-control rounded-3" value="{{ $grupo->periodo_lectivo }}" maxlength="50" required>
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
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                No hay grupos oficiales registrados todavía. Haz clic en <strong>"Nuevo Grupo Oficial"</strong> para crear el primero.
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

        <!-- Modal Crear Nuevo Grupo Oficial desde Administrador -->
        <div class="modal fade" id="modalCrearGrupoAdmin" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold">
                            Crear Grupo Oficial Universitario
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
                                    <input type="text" name="carrera" class="form-control rounded-3" list="listaCarrerasAdmin" maxlength="120" required>
                                    <datalist id="listaCarrerasAdmin">
                                        <option value="Ingeniería en Sistemas Computacionales"></option>
                                        <option value="Ingeniería en Tecnologías de la Información"></option>
                                        <option value="Ingeniería Industrial"></option>
                                        <option value="Ingeniería Mecatrónica"></option>
                                        <option value="Licenciatura en Administración"></option>
                                        <option value="Licenciatura en Contaduría Pública"></option>
                                        <option value="Licenciatura en Derecho"></option>
                                        <option value="Arquitectura"></option>
                                    </datalist>
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
                            btn.innerHTML = '<i class="bi bi-check2 text-success"></i>';
                            setTimeout(function () {
                                btn.innerHTML = iconoOriginal;
                            }, 1500);
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>

