<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Justificantes | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('alumno.dashboard') }}">SAE QR | Justificantes</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('alumno.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                </a>
                @include('partials.campana')
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        @if(session('success'))
            <div class="alert alert-success rounded-3 shadow-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-3 shadow-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <!-- Formulario para subir justificante -->
            <div class="col-lg-5">
                <div class="card rounded-4 p-4 bg-white">
                    <h5 class="titulo-panel mb-4">Subir Comprobante
                        <span class="info-icon" tabindex="0" role="button" aria-label="Elige la inasistencia o retardo a justificar y adjunta tu comprobante.">
                            <i class="bi bi-info-circle"></i>
                            <span class="info-icon-text" role="tooltip">Elige la inasistencia o retardo a justificar y adjunta tu comprobante.</span>
                        </span>
                    </h5>

                    <form id="form-justificante" action="{{ route('alumno.justificantes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div id="alerta-archivo" class="alert alert-danger rounded-3 shadow-sm d-none mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Incidencia a justificar <span class="text-danger">*</span></label>
                            <select name="registro_id" class="form-select" required>
                                <option value="">Selecciona una sesión</option>
                                @foreach($incidencias as $inc)
                                    @if(!$inc->justificante || $inc->justificante->estado_aprobacion !== 'aceptado')
                                        <option value="{{ $inc->id }}" {{ (isset($registroSeleccionado) && (int) $registroSeleccionado === (int) $inc->id) ? 'selected' : '' }}>
                                            {{ $inc->sesion->fecha }} - {{ $inc->sesion->materia->nombre_materia }} ({{ strtoupper($inc->estado_incidencia) }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Motivo de la inasistencia <span class="text-danger">*</span></label>
                            <textarea name="motivo" rows="3" class="form-control" placeholder="Describe brevemente el motivo médico o académico..." required>{{ old('motivo') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Subir Justificante
                                <span class="info-icon" tabindex="0" role="button" aria-label="Formatos permitidos: PDF, JPG, JPEG y PNG. Tamaño máximo: 5 MB.">
                                    <i class="bi bi-info-circle"></i>
                                    <span class="info-icon-text" role="tooltip">Formatos permitidos: PDF, JPG, JPEG y PNG. Tamaño máximo: 5 MB.</span>
                                </span>
                            </label>
                            <input type="file" id="input-documento" name="documento" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-send-check me-2"></i>Enviar Justificante
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historial de justificantes enviados -->
            <div class="col-lg-7">
                <div class="card rounded-4 p-4 bg-white h-100">
                    <h5 class="titulo-panel mb-3">Historial de Solicitudes</h5>
                    <div class="table-responsive">
                        <table class="table table-moderna table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha Clase</th>
                                    <th>Materia</th>
                                    <th>Motivo y Observación Docente</th>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($misJustificantes as $just)
                                    <tr>
                                        <td>{{ $just->registroAsistencia->sesion->fecha }}</td>
                                        <td>{{ $just->registroAsistencia->sesion->materia->nombre_materia }}</td>
                                        <td class="small">
                                            <div>{{ $just->motivo }}</div>
                                            @if($just->comentario_profesor)
                                                <div class="mt-1 p-2 rounded-3 bg-light border-start border-3 {{ $just->estado_aprobacion === 'rechazado' ? 'border-danger text-danger' : 'border-primary text-dark' }}">
                                                    <i class="bi bi-chat-quote-fill me-1"></i><strong>Docente:</strong> {{ $just->comentario_profesor }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($just->documento_url)
                                                <a href="{{ asset('storage/' . $just->documento_url) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                                    <i class="bi bi-file-earmark-text"></i> Ver
                                                </a>
                                            @else
                                                <span class="texto-slate small">Sin archivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($just->estado_aprobacion === 'pendiente')
                                                <span class="badge badge-estado badge-retardo">Pendiente</span>
                                            @elseif($just->estado_aprobacion === 'aceptado')
                                                <span class="badge badge-estado badge-asistencia">Aceptado (Permiso)</span>
                                            @else
                                                <span class="badge badge-estado badge-falta">Rechazado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center texto-slate py-4">No has enviado ningún justificante todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const inputDocumento = document.getElementById('input-documento');
        const alertaArchivo = document.getElementById('alerta-archivo');
        const extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        const maxBytes = 5 * 1024 * 1024; // 5 MB

        function validarArchivo() {
            alertaArchivo.classList.add('d-none');
            alertaArchivo.innerText = '';

            if (!inputDocumento.files || inputDocumento.files.length === 0) {
                return true;
            }

            const archivo = inputDocumento.files[0];
            const nombrePartes = archivo.name.split('.');
            const extension = nombrePartes.length > 1 ? nombrePartes.pop().toLowerCase() : '';

            if (!extensionesPermitidas.includes(extension)) {
                alertaArchivo.innerText = 'Formato no válido. Solo se permiten archivos PDF, JPG, JPEG o PNG.';
                alertaArchivo.classList.remove('d-none');
                inputDocumento.value = '';
                return false;
            }

            if (archivo.size > maxBytes) {
                alertaArchivo.innerText = 'El archivo seleccionado supera el tamaño máximo permitido de 5 MB.';
                alertaArchivo.classList.remove('d-none');
                inputDocumento.value = '';
                return false;
            }

            return true;
        }

        inputDocumento.addEventListener('change', validarArchivo);
        document.getElementById('form-justificante').addEventListener('submit', function (e) {
            if (!validarArchivo()) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
