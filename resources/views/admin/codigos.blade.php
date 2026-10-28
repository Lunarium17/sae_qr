<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos de Acceso Docente - SAE QR</title>
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
                <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-light btn-sm rounded-3">
                    <i class="bi bi-people-fill me-1"></i>Usuarios
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0"><i class="bi bi-key-fill text-primary me-2"></i>Códigos de Acceso Docente</h3>
                <p class="text-muted mb-0">Genera códigos únicos de un solo uso para autorizar el registro de nuevos profesores.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>Volver al Panel
            </a>
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

        <!-- Formulario para generar nuevo código -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Generar Nuevo Código Docente</h5>
                <form action="{{ route('admin.codigos.generar') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label for="descripcion_nota" class="form-label fw-semibold">Nota / Profesor Destinatario (Opcional)</label>
                        <input type="text" class="form-control rounded-3" id="descripcion_nota" name="descripcion_nota" value="{{ old('descripcion_nota') }}" maxlength="150" placeholder="Ej. Entregar al Prof. Carlos Méndez (Matemáticas)">
                    </div>
                    <div class="col-md-4">
                        <label for="codigo_personalizado" class="form-label fw-semibold">Código Personalizado (Opcional)</label>
                        <input type="text" class="form-control rounded-3 text-uppercase font-monospace" id="codigo_personalizado" name="codigo_personalizado" value="{{ old('codigo_personalizado') }}" maxlength="30" placeholder="Dejar vacío para autogenerar">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-key me-1"></i>Generar Código Único
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de códigos generados -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Historial de Códigos ({{ $codigos->count() }})</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nota / Destinatario</th>
                                <th>Estado</th>
                                <th>Canjeado por</th>
                                <th>Fecha de Creación</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($codigos as $cod)
                                <tr>
                                    <td>
                                        <span class="fw-bold font-monospace fs-6 text-primary">{{ $cod->codigo }}</span>
                                    </td>
                                    <td>
                                        {{ $cod->descripcion_nota ?: '—' }}
                                    </td>
                                    <td>
                                        @if ($cod->usado)
                                            <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                                <i class="bi bi-check2-all me-1"></i>Canjeado
                                            </span>
                                        @else
                                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-unlock-fill me-1"></i>Disponible (1 uso)
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($cod->usado && $cod->usadoPor)
                                            <div class="fw-semibold">{{ $cod->usadoPor->name }}</div>
                                            <small class="text-muted">{{ $cod->usadoPor->email }}</small>
                                            @if ($cod->usado_en)
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    Usado el {{ $cod->usado_en->format('d/m/Y H:i') }}
                                                </div>
                                            @endif
                                        @elseif ($cod->usado)
                                            <span class="text-muted fst-italic">Usuario eliminado</span>
                                        @else
                                            <span class="text-muted small">Sin utilizar aún</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $cod->created_at ? $cod->created_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if (!$cod->usado)
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 js-copiar-codigo" data-codigo="{{ $cod->codigo }}">
                                                    <i class="bi bi-clipboard me-1"></i>Copiar
                                                </button>
                                            @endif
                                            <form action="{{ route('admin.codigos.eliminar', $cod->id) }}" method="POST" class="d-inline js-form-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="Eliminar código">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay códigos registrados todavía. Utiliza el formulario superior para generar el primero.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-copiar-codigo').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const codigo = btn.getAttribute('data-codigo');
                    if (navigator.clipboard && codigo) {
                        navigator.clipboard.writeText(codigo).then(function () {
                            const original = btn.innerHTML;
                            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>¡Copiado!';
                            btn.classList.replace('btn-outline-primary', 'btn-success');
                            setTimeout(function () {
                                btn.innerHTML = original;
                                btn.classList.replace('btn-success', 'btn-outline-primary');
                            }, 1600);
                        });
                    }
                });
            });

            document.querySelectorAll('.js-form-eliminar').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este código docente?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>

