<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - SAE QR</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <!-- Cabecera -->
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">SAE_QR</h2>
                            <p class="text-muted mb-0">Registro de Nueva Cuenta</p>
                        </div>

                        <!-- Errores de validación -->
                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Formulario de Registro -->
                        <form action="{{ route('register.store') }}" method="POST" id="formRegistro">
                            @csrf

                            <!-- Tipo de Cuenta (Rol) -->
                            <div class="mb-3">
                                <label for="rol" class="form-label fw-semibold">Tipo de Cuenta <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="rol" name="rol" required>
                                    <option value="alumno" {{ old('rol', 'alumno') === 'alumno' ? 'selected' : '' }}>
                                        Alumno (Estudiante)
                                    </option>
                                    <option value="profesor" {{ old('rol') === 'profesor' ? 'selected' : '' }}>
                                        Profesor (Docente)
                                    </option>
                                </select>
                            </div>

                            <!-- Nombre Completo -->
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Ej. Juan Pérez García">
                            </div>

                            <!-- Correo Electrónico -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control rounded-3" id="email" name="email" value="{{ old('email') }}" required maxlength="150" placeholder="ejemplo@saeqr.com">
                            </div>

                            <!-- Matrícula / Clave Institucional -->
                            <div class="mb-3" id="bloqueMatricula">
                                <label for="matricula_codigo" class="form-label fw-semibold" id="labelMatricula">
                                    Matrícula Escolar <span class="text-danger" id="asteriscoMatricula">*</span>
                                </label>
                                <input type="text" class="form-control rounded-3" id="matricula_codigo" name="matricula_codigo" value="{{ old('matricula_codigo') }}" maxlength="30" placeholder="Ej. 20260001">
                                <div class="form-text" id="ayudaMatricula">
                                    Tu matrícula escolar servirá para generar tu código QR de asistencia.
                                </div>
                            </div>

                            <!-- Código de Acceso Docente (de un solo uso, generado por el Administrador) -->
                            <div class="mb-3 d-none" id="bloqueCodigoDocente">
                                <label for="codigo_docente" class="form-label fw-semibold text-primary">
                                    Código de Acceso Docente <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control rounded-3 border-primary text-uppercase" id="codigo_docente" name="codigo_docente" maxlength="30" placeholder="Ej. DOC-XXXX-26">
                                <div class="form-text">
                                    Ingresa el código único de invitación proporcionado por el Administrador Escolar.
                                </div>
                            </div>

                            <!-- Contraseña y Confirmación -->
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label for="password" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control rounded-3" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                                </div>
                                <div class="col-sm-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control rounded-3" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="Repite tu contraseña">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-3 py-2 fw-semibold">
                                    Crear mi Cuenta
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <span class="text-muted small">¿Ya tienes una cuenta registrada?</span>
                            <a href="{{ route('login') }}" class="fw-semibold text-decoration-none ms-1">Inicia sesión aquí</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectRol = document.getElementById('rol');
            const inputMatricula = document.getElementById('matricula_codigo');
            const labelMatricula = document.getElementById('labelMatricula');
            const ayudaMatricula = document.getElementById('ayudaMatricula');
            const bloqueCodigoDocente = document.getElementById('bloqueCodigoDocente');
            const inputCodigoDocente = document.getElementById('codigo_docente');

            function actualizarCamposPorRol() {
                const esProfesor = selectRol.value === 'profesor';

                if (esProfesor) {
                    bloqueCodigoDocente.classList.remove('d-none');
                    inputCodigoDocente.setAttribute('required', 'required');

                    inputMatricula.removeAttribute('required');
                    labelMatricula.innerHTML = 'Clave / Número de Empleado <span class="text-muted fw-normal small">(Opcional)</span>';
                    inputMatricula.setAttribute('placeholder', 'Ej. DOC-001 (Opcional)');
                    ayudaMatricula.textContent = 'Opcional para docentes. Puedes dejarlo en blanco o registrar tu número de empleado.';
                } else {
                    bloqueCodigoDocente.classList.add('d-none');
                    inputCodigoDocente.removeAttribute('required');
                    inputCodigoDocente.value = '';

                    inputMatricula.setAttribute('required', 'required');
                    labelMatricula.innerHTML = 'Matrícula Escolar <span class="text-danger">*</span>';
                    inputMatricula.setAttribute('placeholder', 'Ej. 20260001');
                    ayudaMatricula.textContent = 'Tu matrícula escolar servirá para generar tu código QR de asistencia.';
                }
            }

            selectRol.addEventListener('change', actualizarCamposPorRol);
            actualizarCamposPorRol();
        });
    </script>
</body>
</html>
