<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('alumno.dashboard') }}">
                <i class="bi bi-arrow-left-circle me-2"></i>Volver a mi Panel
            </a>
            <span class="navbar-text text-white fw-bold">Mis Datos Personales</span>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-primary text-white p-4 border-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
                                {{ strtoupper(substr($alumno->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $alumno->name }}</h4>
                                <span class="small opacity-75">Perfil de Estudiante | {{ $alumno->matricula_codigo ? 'Matrícula: ' . $alumno->matricula_codigo : 'Sin matrícula asignada' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('alumno.perfil.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <h6 class="fw-bold text-uppercase text-muted small mb-3">
                                <i class="bi bi-person-lines-fill me-1"></i>Información General
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $alumno->name) }}" class="form-control rounded-3" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Correo Institucional / Personal <span class="text-danger">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $alumno->email) }}" class="form-control rounded-3" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Matrícula / Número de Control</label>
                                    <input type="text" name="matricula_codigo" value="{{ old('matricula_codigo', $alumno->matricula_codigo) }}" class="form-control rounded-3" placeholder="Ej. 202600123" maxlength="30">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-uppercase text-muted small mb-1">
                                <i class="bi bi-shield-lock-fill me-1"></i>Seguridad y Contraseña
                            </h6>
                            <p class="text-muted small mb-3">Deja estos campos en blanco si no deseas cambiar tu contraseña actual.</p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nueva Contraseña</label>
                                    <input type="password" name="password" class="form-control rounded-3" placeholder="Mínimo 6 caracteres">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="Repite la nueva contraseña">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('alumno.dashboard') }}" class="btn btn-light rounded-3 px-4 py-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold">
                                    <i class="bi bi-check2-circle me-1"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

