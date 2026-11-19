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
    @include('partials.ui_helpers')

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold js-link-salida" href="{{ route('alumno.dashboard') }}"><i class="bi bi-person-circle me-2"></i>SAE QR | Mis Datos Personales</a>
            <a href="{{ route('alumno.dashboard') }}" class="btn btn-light text-primary fw-semibold btn-sm rounded-3 js-link-salida">
                <i class="bi bi-arrow-left me-1"></i>Volver a mi Panel
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header text-white p-4 border-0" style="background-color: var(--pantone-294c);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                @if ($alumno->foto_perfil)
                                    <img id="previewAvatarAlumno" src="{{ asset('storage/' . $alumno->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle object-fit-cover border border-2 border-white shadow-sm" style="width: 60px; height: 60px;">
                                @else
                                    <div id="avatarInicialAlumno" class="bg-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 60px; height: 60px; color: var(--pantone-294c);">
                                        {{ strtoupper(substr($alumno->name, 0, 1)) }}
                                    </div>
                                    <img id="previewAvatarAlumno" src="" alt="Vista previa" class="rounded-circle object-fit-cover border border-2 border-white shadow-sm d-none" style="width: 60px; height: 60px;">
                                @endif
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $alumno->name }}</h4>
                                <span class="small opacity-75">Perfil de Estudiante | {{ $alumno->matricula_codigo ? 'Matrícula: ' . $alumno->matricula_codigo : 'Sin matrícula asignada' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('alumno.perfil.update') }}" method="POST" enctype="multipart/form-data" id="formPerfilAlumno">
                            @csrf
                            @method('PUT')

                            <h6 class="fw-bold text-uppercase text-muted small mb-3">
                                <i class="bi bi-person-lines-fill me-1"></i>Información General
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Foto de Perfil</label>
                                    <input type="file" name="foto_perfil" id="inputFotoPerfilAlumno" class="form-control rounded-3" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                </div>

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
                                <i class="bi bi-shield-lock-fill me-1"></i>Seguridad y Contraseña
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nueva Contraseña</label>
                                    <input type="password" name="password" id="inputPassNueva" class="form-control rounded-3" placeholder="Mínimo 6 caracteres">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirmation" id="inputPassConfirm" class="form-control rounded-3" placeholder="Repite la nueva contraseña">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('alumno.dashboard') }}" class="btn btn-light rounded-3 px-4 py-2 js-link-salida">Cancelar</a>
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
            const form = document.getElementById('formPerfilAlumno');
            const pass1 = document.getElementById('inputPassNueva');
            const pass2 = document.getElementById('inputPassConfirm');
            const inputFoto = document.getElementById('inputFotoPerfilAlumno');
            const previewImg = document.getElementById('previewAvatarAlumno');
            const avatarInicial = document.getElementById('avatarInicialAlumno');
            let guardando = false;

            if (inputFoto) {
                inputFoto.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            if (previewImg) {
                                previewImg.src = e.target.result;
                                previewImg.classList.remove('d-none');
                            }
                            if (avatarInicial) {
                                avatarInicial.classList.add('d-none');
                            }
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            function tienePasswordSinGuardar() {
                return !guardando && ((pass1 && pass1.value.length > 0) || (pass2 && pass2.value.length > 0));
            }

            if (form) {
                form.addEventListener('submit', function () {
                    guardando = true;
                });
            }

            document.querySelectorAll('.js-link-salida').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    if (tienePasswordSinGuardar()) {
                        const salir = confirm('Escribiste caracteres en la sección de contraseña sin guardar los cambios. ¿Deseas salir y descartar el cambio de contraseña?');
                        if (!salir) {
                            e.preventDefault();
                        } else {
                            guardando = true;
                        }
                    }
                });
            });

            window.addEventListener('beforeunload', function (e) {
                if (tienePasswordSinGuardar()) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
</body>
</html>
