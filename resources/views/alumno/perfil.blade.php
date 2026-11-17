<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.info-icon-css')
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="btn btn-light btn-sm text-primary fw-semibold rounded-3" href="{{ route('alumno.dashboard') }}">
                <i class="bi bi-arrow-left me-1"></i>Volver a mi Panel
            </a>
               @include('partials.campana')
               <span class="navbar-text text-white fw-bold">Mis Datos Personales</span>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                        {{ session('success') }}
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
                            @if ($alumno->foto_perfil)
                                <img src="{{ asset('storage/' . $alumno->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 52px; height: 52px; object-fit: cover; border: 2px solid rgba(255,255,255,.6);">
                            @else
                                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 52px; height: 52px;">
                                    {{ strtoupper(substr($alumno->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $alumno->name }}</h4>
                                <span class="small opacity-75">Perfil de Estudiante | {{ $alumno->matricula_codigo ? 'Matrícula: ' . $alumno->matricula_codigo : 'Sin matrícula asignada' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('alumno.perfil.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <h6 class="fw-bold text-uppercase text-muted small mb-3">
                                Foto de Perfil
                            </h6>

                            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                                <div class="rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 88px; height: 88px;">
                                    <img id="previewFotoPerfil" src="{{ $alumno->foto_perfil ? asset('storage/' . $alumno->foto_perfil) : '' }}" class="{{ $alumno->foto_perfil ? '' : 'd-none' }}" alt="Vista previa" style="width: 88px; height: 88px; object-fit: cover;">
                                    @if (!$alumno->foto_perfil)
                                        <span id="inicialesFotoPerfil" class="fw-bold fs-3 text-secondary">{{ strtoupper(substr($alumno->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1" style="min-width: 220px;">
                                    <label class="form-label fw-semibold mb-0" for="inputFotoPerfil">
                                        Cambiar foto
                                        <span class="info-icon" tabindex="0" role="button" aria-label="JPG, PNG o WEBP, máximo 2 MB. La foto se actualizará al guardar los cambios.">
                                            <i class="bi bi-info-circle"></i>
                                            <span class="info-icon-text" role="tooltip">JPG, PNG o WEBP · máximo 2 MB. La foto se actualizará al guardar los cambios.</span>
                                        </span>
                                    </label>
                                    <input type="file" name="foto_perfil" id="inputFotoPerfil" accept="image/jpeg,image/png,image/webp" class="form-control rounded-3">
                                    @if ($alumno->foto_perfil)
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="eliminar_foto" id="eliminarFoto" value="1">
                                            <label class="form-check-label small" for="eliminarFoto">Quitar mi foto actual</label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-uppercase text-muted small mb-3">
                                Información General
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
                                    <input type="text" name="matricula_codigo" value="{{ old('matricula_codigo', $alumno->matricula_codigo) }}" class="form-control rounded-3" maxlength="30">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-uppercase text-muted small mb-3">
                                Seguridad y Contraseña
                            </h6>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('inputFotoPerfil');
            const preview = document.getElementById('previewFotoPerfil');
            const iniciales = document.getElementById('inicialesFotoPerfil');

            if (input && preview) {
                input.addEventListener('change', function () {
                    const archivo = input.files && input.files[0];
                    if (!archivo) return;
                    const lector = new FileReader();
                    lector.onload = function (e) {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        if (iniciales) iniciales.classList.add('d-none');
                    };
                    lector.readAsDataURL(archivo);
                });
            }
        });
    </script>
</body>
</html>

