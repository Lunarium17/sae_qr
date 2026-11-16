<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Materia - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .info-icon { position: relative; display: inline-flex; align-items: center; color: #0d6efd; cursor: help; vertical-align: middle; }
        .info-icon .info-icon-text { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); min-width: 230px; max-width: 310px; background: #212529; color: #fff; font-size: .75rem; font-weight: 400; line-height: 1.35; padding: .5rem .65rem; border-radius: .4rem; box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .2); opacity: 0; visibility: hidden; transition: opacity .15s; z-index: 10; text-align: left; }
        .info-icon:hover .info-icon-text, .info-icon:focus .info-icon-text, .info-icon:focus-within .info-icon-text { opacity: 1; visibility: visible; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Registrar Materia</a>
            <a href="{{ route('materias.index') }}" class="btn btn-light btn-sm text-dark fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Volver al Listado
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h4 class="fw-bold text-dark mb-4">
                        Registrar Nueva Materia
                        <span class="info-icon" tabindex="0" role="button" aria-label="Ingresa los datos de la asignatura para el catálogo académico.">
                            <i class="bi bi-info-circle"></i>
                            <span class="info-icon-text" role="tooltip">Ingresa los datos de la asignatura para el catálogo académico.</span>
                        </span>
                    </h4>

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
                            <input type="text" name="codigo" id="codigo" class="form-control rounded-3" value="{{ old('codigo') }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="nombre_materia" class="form-label fw-semibold">Nombre de la Materia</label>
                            <input type="text" name="nombre_materia" id="nombre_materia" class="form-control rounded-3" value="{{ old('nombre_materia') }}" required>
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