<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-shield-lock-fill text-info me-2"></i>SAE QR | Panel de Administrador
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm rounded-3">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
                <a href="{{ route('admin.codigos') }}" class="btn btn-outline-info btn-sm rounded-3">
                    <i class="bi bi-key-fill me-1"></i>Códigos Docentes
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h3 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Directorio y Gestión de Usuarios</h3>
                <p class="text-muted mb-0">Administra cuentas de profesores y alumnos, edita sus datos o restablece contraseñas olvidadas.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                    <i class="bi bi-person-plus-fill me-1"></i>Nuevo Usuario
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                </a>
            </div>
        </div>

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

        <!-- Barra de búsqueda y filtro por rol -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.usuarios') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="rol" class="form-label fw-semibold small">Filtrar por Rol</label>
                        <select name="rol" id="rol" class="form-select rounded-3">
                            <option value="todos" {{ $rolFiltro === 'todos' ? 'selected' : '' }}>Todos los roles</option>
                            <option value="profesor" {{ $rolFiltro === 'profesor' ? 'selected' : '' }}>Profesores</option>
                            <option value="alumno" {{ $rolFiltro === 'alumno' ? 'selected' : '' }}>Alumnos</option>
                            <option value="admin" {{ $rolFiltro === 'admin' ? 'selected' : '' }}>Administradores</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="buscar" class="form-label fw-semibold small">Buscar por Nombre, Correo o Matrícula</label>
                        <input type="text" name="buscar" id="buscar" value="{{ $buscar }}" class="form-control rounded-3" placeholder="Ej. Juan Pérez, correo@saeqr.com o matrícula...">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-3">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-secondary rounded-3" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Usuarios Registrados</h5>
                <span class="badge bg-secondary rounded-pill">{{ $usuarios->total() }} usuario(s)</span>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Correo Electrónico</th>
                                <th>Rol</th>
                                <th>Matrícula / Clave</th>
                                <th>Registro</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $usr)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $usr->name }}
                                        @if ($usr->id === \Illuminate\Support\Facades\Auth::id())
                                            <span class="badge bg-secondary ms-1">Tú</span>
                                        @endif
                                    </td>
                                    <td>{{ $usr->email }}</td>
                                    <td>
                                        @if ($usr->rol === 'admin')
                                            <span class="badge bg-info text-dark px-3 py-2 rounded-pill">Administrador</span>
                                        @elseif ($usr->rol === 'profesor')
                                            <span class="badge bg-success px-3 py-2 rounded-pill">Profesor</span>
                                        @else
                                            <span class="badge bg-primary px-3 py-2 rounded-pill">Alumno</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($usr->matricula_codigo)
                                            <span class="font-monospace fw-semibold">{{ $usr->matricula_codigo }}</span>
                                        @else
                                            <span class="text-muted small">No asignada</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $usr->created_at ? $usr->created_at->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm rounded-3 js-btn-editar-usuario"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarUsuario"
                                                data-action="{{ route('admin.usuarios.update', $usr->id) }}"
                                                data-name="{{ $usr->name }}"
                                                data-email="{{ $usr->email }}"
                                                data-rol="{{ $usr->rol }}"
                                                data-matricula="{{ $usr->matricula_codigo ?? '' }}"
                                            >
                                                <i class="bi bi-pencil-square me-1"></i>Editar / Clave
                                            </button>

                                            @if ($usr->id !== \Illuminate\Support\Facades\Auth::id())
                                                <form action="{{ route('admin.usuarios.destroy', $usr->id) }}" method="POST" class="d-inline js-form-eliminar-usuario">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="Eliminar cuenta">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No se encontraron usuarios con los criterios seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($usuarios->hasPages())
                    <div class="pt-3 mt-3 border-top">
                        {{ $usuarios->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Modal: Crear Nuevo Usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-primary me-2"></i>Registrar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('admin.usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rol del Usuario <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select rounded-3" required>
                                <option value="alumno">Alumno (Estudiante)</option>
                                <option value="profesor">Profesor (Docente)</option>
                                <option value="admin">Administrador Escolar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" required maxlength="100" placeholder="Ej. María López Hernández">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control rounded-3" required maxlength="150" placeholder="usuario@saeqr.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Matrícula / Clave (Obligatoria para Alumnos)</label>
                            <input type="text" name="matricula_codigo" class="form-control rounded-3" maxlength="30" placeholder="Ej. 20260001 o DOC-01">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Contraseña Inicial <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control rounded-3" required minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Crear Cuenta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Usuario / Restablecer Contraseña -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Usuario / Restablecer Clave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formEditarUsuario" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rol del Usuario <span class="text-danger">*</span></label>
                            <select name="rol" id="editRol" class="form-select rounded-3" required>
                                <option value="alumno">Alumno (Estudiante)</option>
                                <option value="profesor">Profesor (Docente)</option>
                                <option value="admin">Administrador Escolar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control rounded-3" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control rounded-3" required maxlength="150">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Matrícula / Clave</label>
                            <input type="text" name="matricula_codigo" id="editMatricula" class="form-control rounded-3" maxlength="30">
                        </div>
                        <hr>
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-primary">
                                <i class="bi bi-key-fill me-1"></i>Nueva Contraseña (Opcional)
                            </label>
                            <input type="password" name="password" class="form-control rounded-3 border-primary" minlength="6" placeholder="Dejar en blanco para conservar la actual">
                            <div class="form-text">
                                Si el usuario olvidó su contraseña, escribe aquí una nueva clave (mínimo 6 caracteres).
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formEditar = document.getElementById('formEditarUsuario');
            const editName = document.getElementById('editName');
            const editEmail = document.getElementById('editEmail');
            const editRol = document.getElementById('editRol');
            const editMatricula = document.getElementById('editMatricula');

            document.querySelectorAll('.js-btn-editar-usuario').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    formEditar.setAttribute('action', btn.getAttribute('data-action'));
                    editName.value = btn.getAttribute('data-name') || '';
                    editEmail.value = btn.getAttribute('data-email') || '';
                    editRol.value = btn.getAttribute('data-rol') || 'alumno';
                    editMatricula.value = btn.getAttribute('data-matricula') || '';
                });
            });

            document.querySelectorAll('.js-form-eliminar-usuario').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!confirm('¿Estás seguro de que deseas eliminar permanentemente esta cuenta de usuario?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>

