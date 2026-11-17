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
        .info-icon { position: relative; display: inline-flex; align-items: center; color: #0d6efd; cursor: help; vertical-align: middle; font-size: .8em; margin-left: .15rem; padding: .1rem; }
        .info-icon .info-icon-text { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); min-width: 220px; max-width: 300px; background: #212529; color: #fff; font-size: .75rem; font-weight: 400; line-height: 1.35; padding: .45rem .6rem; border-radius: .4rem; box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .2); opacity: 0; visibility: hidden; transition: opacity .15s; z-index: 10; text-align: left; }
        .info-icon:hover .info-icon-text, .info-icon:focus .info-icon-text, .info-icon:focus-within .info-icon-text { opacity: 1; visibility: visible; }
        .bg-sae-dark { background: linear-gradient(160deg, #1e2124 0%, #0d4b96 100%); }
    </style>
</head>
<body class="bg-sae-dark d-flex align-items-center justify-content-center min-vh-100 py-4">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Cabecera -->
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">SAE_QR</h2>
                            <p class="text-muted">Sistema de Asistencia Escolar</p>
                        </div>

                        <!-- Mensaje de éxito tras crear cuenta -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif

                        <!-- Mensaje de sesión expirada (token vencido) -->
                        @if (session('error'))
                            <div class="alert alert-warning alert-dismissible fade show py-2 small" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif

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

                        <!-- Formulario de Login -->
                        <form action="{{ url('/login') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico
                                    <span class="info-icon" tabindex="0" role="button" aria-label="Usa tu correo institucional.">
                                        <i class="bi bi-info-circle"></i>
                                        <span class="info-icon-text" role="tooltip">Usa tu correo institucional.</span>
                                    </span>
                                </label>
                                <input type="email" class="form-control rounded-3" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control rounded-3" id="password" name="password" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-3 py-2 fw-semibold">Ingresar al SAE</button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <span class="text-muted small">¿No tienes una cuenta?</span>
                            <a href="{{ route('register') }}" class="fw-semibold text-decoration-none ms-1">Crear cuenta aquí</a>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">¿Problemas con tu acceso? Contacta al administrador escolar.</small>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
