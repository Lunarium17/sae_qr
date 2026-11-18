<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo {{ $grupo->nombre_grupo }} - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('grupos.index') }}">
                SAE QR | Editar Grupo
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('grupos.show', $grupo->id) }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    <i class="bi bi-journal-bookmark-fill me-1"></i>Ver Materias del Grupo
                </a>
                <a href="{{ route('grupos.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Mis Grupos
                </a>
                @include('partials.campana')
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card rounded-4 bg-white overflow-hidden">
                    <div class="card-header franja-institucional text-white p-4 border-0">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Grupo Académico: {{ $grupo->nombre_grupo }}</h4>
                    </div>
                    <div class="card-body p-5">
                        <form action="{{ route('grupos.update', $grupo->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-4 mb-4">
                                <!-- Nombre del Grupo -->
                                <div class="col-md-4">
                                    <label for="nombre_grupo" class="form-label fw-bold text-dark">Clave / Nombre del Grupo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3 py-2" id="nombre_grupo" name="nombre_grupo" value="{{ old('nombre_grupo', $grupo->nombre_grupo) }}" required>
                                </div>

                                <!-- Carrera -->
                                <div class="col-md-8">
                                    <label for="carrera" class="form-label fw-bold text-dark">Carrera / Programa Educativo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3 py-2" id="carrera" name="carrera" value="{{ old('carrera', $grupo->carrera) }}" maxlength="120" required placeholder="Escribe el nombre de la carrera o programa educativo">
                                </div>
                                
                                <!-- Semestre -->
                                <div class="col-md-4">
                                    <label for="grado" class="form-label fw-bold text-dark">Semestre <span class="text-danger">*</span></label>
                                    @php
                                        $gradoActual = old('grado', $grupo->grado);
                                        $semestresLista = [];
                                        for ($i = 1; $i <= 12; $i++) {
                                            $semestresLista[] = $i . '° Semestre';
                                        }
                                    @endphp
                                    <select class="form-select rounded-3 py-2" id="grado" name="grado" required>
                                        @if ($gradoActual && !in_array($gradoActual, $semestresLista))
                                            <option value="{{ $gradoActual }}" selected>{{ $gradoActual }}</option>
                                        @endif
                                        @foreach ($semestresLista as $sem)
                                            <option value="{{ $sem }}" {{ $gradoActual === $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Turno -->
                                <div class="col-md-4">
                                    <label for="turno" class="form-label fw-bold text-dark">Turno / Modalidad <span class="text-danger">*</span></label>
                                    @php $turnoActual = old('turno', $grupo->turno); @endphp
                                    <select class="form-select rounded-3 py-2" id="turno" name="turno" required>
                                        <option value="Matutino" {{ $turnoActual === 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                        <option value="Vespertino" {{ $turnoActual === 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                        <option value="Nocturno" {{ $turnoActual === 'Nocturno' ? 'selected' : '' }}>Nocturno</option>
                                        <option value="Mixto / Sabatino" {{ $turnoActual === 'Mixto / Sabatino' ? 'selected' : '' }}>Mixto / Sabatino</option>
                                    </select>
                                </div>
                                
                                <!-- Periodo Lectivo -->
                                <div class="col-md-4">
                                    <label for="periodo_lectivo" class="form-label fw-bold text-dark">Periodo Semestral <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3 py-2" id="periodo_lectivo" name="periodo_lectivo" value="{{ old('periodo_lectivo', $grupo->periodo_lectivo) }}" required>
                                </div>
                            </div>

                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('grupos.index') }}" class="btn btn-light rounded-3 px-4 py-2">Cancelar</a>
                                <button type="submit" class="btn btn-institucional fw-semibold rounded-3 px-4 py-2">
                                    <i class="bi bi-check2-circle me-2"></i>Guardar Cambios
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

