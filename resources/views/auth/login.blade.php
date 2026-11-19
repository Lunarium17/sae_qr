<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SAE QR</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="theme-pantone-294 d-flex align-items-center justify-content-center min-vh-100 py-4">
    @include('partials.ui_helpers')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Cabecera con Logo Oficial SAE_QR -->
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/logo_sae_qr.jpg') }}" alt="SAE_QR - Sistema de Asistencia Escolar" class="sae-auth-logo">
                        </div>

                        <!-- Formulario de Login -->
                        <form action="{{ url('/login') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label d-flex align-items-center">
                                    <span>Correo Electrónico:</span>
                                    <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Debe ser tu correo institucional">!</span>
                                </label>
                                <input type="email" class="form-control rounded-3" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña:</label>
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
