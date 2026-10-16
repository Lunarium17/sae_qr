<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Grupo - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-card-list me-2"></i>SAE QR | Gestión de Grupos</a>
            <div class="d-flex align-items-center">
                <!-- Botón para regresar al Listado de Grupos -->
                <a href="{{ url('/profesor/grupos') }}" class="btn btn-outline-light btn-sm me-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Mis Grupos
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-primary text-white p-4 border-0">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>Crear Nuevo Grupo Académico</h4>
                    </div>
                    <div class="card-body p-5">
                        
                        <!-- Formulario conectado a la ruta 'store' del Controlador -->
                        <form action="{{ route('grupos.store') }}" method="POST">
                            @csrf
                            
                            <div class="row g-4 mb-4">
                                <!-- Nombre del Grupo -->
                                <div class="col-md-6">
                                    <label for="nombre_grupo" class="form-label fw-bold text-dark">Nombre del Grupo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3 py-2" id="nombre_grupo" name="nombre_grupo" placeholder="Ej. S7B, 1A" required>
                                </div>
                                
                                <!-- Grado -->
                                <div class="col-md-6">
                                    <label for="grado" class="form-label fw-bold text-dark">Grado o Semestre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3 py-2" id="grado" name="grado" placeholder="Ej. 7mo Semestre, Primer Año" required>
                                </div>
                                
                                <!-- Turno -->
                                <div class="col-md-6">
                                    <label for="turno" class="form-label fw-bold text-dark">Turno <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3 py-2" id="turno" name="turno" required>
                                        <option value="" selected disabled>Selecciona el turno...</option>
                                        <option value="Matutino">Matutino</option>
                                        <option value="Vespertino">Vespertino</option>
                                        <option value="Nocturno">Nocturno</option>
                                    </select>
                                </div>
                                
                                <!-- Periodo Lectivo -->
                                <div class="col-md-6">
                                    <label for="periodo_lectivo" class="form-label fw-bold text-dark">Periodo Lectivo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3 py-2" id="periodo_lectivo" name="periodo_lectivo" placeholder="Ej. Ago-Dic 2026, Ciclo 2026-A" required>
                                </div>
                            </div>

                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ url('/profesor/grupos') }}" class="btn btn-light rounded-3 px-4 py-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary rounded-3 px-4 py-2">
                                    <i class="bi bi-save me-2"></i>Guardar Grupo
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