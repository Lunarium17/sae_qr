<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Materia | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('grupos.index') }}">
                <i class="bi bi-pencil-square me-2"></i>SAE QR | Editar Materia
            </a>
            <a href="{{ $materia->grupo_id ? route('grupos.show', $materia->grupo_id) : route('grupos.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al Grupo
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
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
                    <div class="card-header bg-warning text-dark p-4 border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Materia: {{ $materia->nombre_materia }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('materias.update', $materia->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="grupo_id" value="{{ $materia->grupo_id }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Código / Clave de la Materia</label>
                                <input type="text" name="codigo" value="{{ old('codigo', $materia->codigo_materia) }}" class="form-control rounded-3" maxlength="30" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Nombre de la Materia</label>
                                <input type="text" name="nombre_materia" value="{{ old('nombre_materia', $materia->nombre_materia) }}" class="form-control rounded-3" maxlength="100" required>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ $materia->grupo_id ? route('grupos.show', $materia->grupo_id) : route('grupos.index') }}" class="btn btn-light rounded-3">Cancelar</a>
                                <button type="submit" class="btn btn-warning fw-semibold rounded-3">
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

