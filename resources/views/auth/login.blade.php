<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SAE QR</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

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
                                <label for="email" class="form-label">Correo Electrónico:</label>
                                <input type="email" class="form-control rounded-3" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="ejemplo@sae_qr.com">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña:</label>
                                <input type="password" class="form-control rounded-3" id="password" name="password" required placeholder="********">
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-3 py-2 fw-semibold">Ingresar al SAE</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
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