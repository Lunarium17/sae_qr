<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Grupos Oficiales - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    @php
        $admin = \Illuminate\Support\Facades\Auth::user();
        $avHue = abs(crc32($admin->name ?? 'U')) % 360;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Catálogo de Grupos</a>
            <div class="d-flex align-items-center gap-2">
                @if ($admin->foto_perfil)
                    <img src="{{ asset('storage/' . $admin->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,.5);">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 34px; height: 34px; background: hsl({{ $avHue }}, 65%, 45%); font-size: .85rem;">{{ mb_strtoupper(mb_substr($admin->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Inicio
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
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
                {{ session('error') }}
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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h3 class="fw-bold text-dark mb-0">Catálogo de Grupos Oficiales</h3>
            <button type="button" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCrearGrupoAdmin">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Grupo Oficial
            </button>
        </div>

        <!-- Catálogo de Grupos Oficiales Universitarios -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body px-4 py-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
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
                                        <div class="fw-bold" style="color: {{ \App\Support\CarreraColor::color($grupo->carrera ?? '') }};">{{ $grupo->nombre_grupo }}</div>
                                        <span class="badge rounded-pill px-2 py-1 mt-1" style="{{ \App\Support\CarreraColor::badge($grupo->carrera ?? '') }}">
                                            <i class="bi bi-mortarboard-fill me-1"></i>{{ $grupo->carrera ?: 'Carrera general' }}
                                        </span>
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
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No hay grupos oficiales registrados todavía. Haz clic en <strong>"Nuevo Grupo Oficial"</strong> para crear el primero.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
</body>
</html>
