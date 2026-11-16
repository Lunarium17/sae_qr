<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SAE QR</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .btn-ojo { z-index: 5; border: 0; background: transparent; }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <!-- Cabecera -->
                        <div class="text-center mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-4 mb-2" style="width: 56px; height: 56px;">
                                <i class="bi bi-qr-code fs-2"></i>
                            </span>
                            <h2 class="fw-bold text-primary mb-1">SAE QR</h2>
                            <p class="text-muted small mb-0">Sistema de Asistencia Escolar</p>
                        </div>

                        <!-- Mensaje de éxito tras crear cuenta -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show py-2 small rounded-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif

                        <!-- Mensaje de sesión expirada (token vencido) -->
                        @if (session('error'))
                            <div class="alert alert-warning alert-dismissible fade show py-2 small rounded-3" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif

                        <!-- Errores de validación -->
                        @if ($errors->any())
                            <div class="alert alert-danger py-2 rounded-3">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Formulario de Login -->
                        <form action="{{ url('/login') }}" method="POST">
                            @csrf

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required autofocus>
                                <label for="email">Correo Electrónico</label>
                            </div>

                            <div class="form-floating position-relative">
                                <input type="password" class="form-control rounded-3 pe-5" id="password" name="password" placeholder="Contraseña" required>
                                <label for="password">Contraseña</label>
                                <button type="button" id="togglePassword" class="btn btn-ojo position-absolute top-50 end-0 translate-middle-y text-muted" tabindex="-1" onmousedown="event.preventDefault();" aria-label="Mostrar u ocultar contraseña">
                                    <i class="bi bi-eye" id="iconoOjo"></i>
                                </button>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary rounded-3 py-2 fw-semibold">Ingresar</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted small">¿No tienes una cuenta?</span>
                            <a href="{{ route('register') }}" class="fw-semibold text-decoration-none ms-1">Crear cuenta aquí</a>
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
            const toggle = document.getElementById('togglePassword');
            const campo = document.getElementById('password');
            const icono = document.getElementById('iconoOjo');
            if (toggle && campo) {
                toggle.addEventListener('click', function () {
                    const visible = campo.type === 'text';
                    campo.type = visible ? 'password' : 'text';
                    icono.classList.toggle('bi-eye', visible);
                    icono.classList.toggle('bi-eye-slash', !visible);
                });
            }
        });
    </script>
</body>
</html>
