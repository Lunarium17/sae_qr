<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Materia - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-journal-plus me-2"></i>SAE QR | Registrar Materia</a>
            <a href="{{ route('materias.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al Listado
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h4 class="fw-bold text-dark mb-1">Registrar Nueva Materia</h4>
                    <p class="text-muted small mb-4">Ingresa los datos de la asignatura para el catálogo académico.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('materias.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="codigo" class="form-label fw-semibold">Código o Clave de la Materia</label>
                            <input type="text" name="codigo" id="codigo" class="form-control rounded-3" placeholder="Ej. ING-SW-01" value="{{ old('codigo') }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="nombre_materia" class="form-label fw-semibold">Nombre de la Materia</label>
                            <input type="text" name="nombre_materia" id="nombre_materia" class="form-control rounded-3" placeholder="Ej. Gestión de Proyectos de Software" value="{{ old('nombre_materia') }}" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-3 py-2 fw-semibold">
                                <i class="bi bi-save me-2"></i>Guardar Materia
                            </button>
                            <a href="{{ route('materias.index') }}" class="btn btn-light rounded-3 py-2 text-muted">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>