<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Justificantes | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    @include('partials.ui_helpers')

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('alumno.dashboard') }}"><i class="bi bi-file-earmark-medical me-2"></i>SAE QR | Carga Digital de Justificantes</a>
            <a href="{{ route('alumno.dashboard') }}" class="btn btn-light text-primary fw-semibold btn-sm rounded-3">
                <i class="bi bi-arrow-left me-1"></i>Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row g-4">
            <!-- Formulario para subir justificante -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-2"><i class="bi bi-upload me-2 text-primary"></i>Subir Comprobante</h5>
                    <p class="text-muted small">Selecciona la inasistencia o retardo que deseas justificar y adjunta tu documento médico o institucional si aplica.</p>

                    <form id="form-justificante" action="{{ route('alumno.justificantes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div id="alerta-archivo" class="alert alert-danger rounded-3 shadow-sm d-none mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Incidencia a justificar <span class="text-danger">*</span></label>
                            <select name="registro_id" class="form-select js-sae-select" data-search-placeholder="Buscar fecha o materia..." required>
                                <option value="" data-title="Selecciona una sesión">Selecciona una sesión</option>
                                @foreach($incidencias as $inc)
                                    @if(!$inc->justificante || $inc->justificante->estado_aprobacion !== 'aceptado')
                                        <option value="{{ $inc->id }}"
                                                data-title="{{ $inc->sesion->materia->nombre_materia }}"
                                                data-badge="{{ strtoupper($inc->estado_incidencia) }}"
                                                data-sub="Fecha de sesión: {{ $inc->sesion->fecha }}"
                                                {{ (isset($registroSeleccionado) && (int) $registroSeleccionado === (int) $inc->id) ? 'selected' : '' }}>
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
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <span>Subir Justificante</span>
                                <span class="info-circle-help" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="Solo se acepta subir archivos PDF, JPG, JPEG o PNG | Máx. 5 MB">!</span>
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
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-secondary"></i>Historial de Solicitudes</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
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
                                                <span class="text-muted small">Sin archivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($just->estado_aprobacion === 'pendiente')
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @elseif($just->estado_aprobacion === 'aceptado')
                                                <span class="badge bg-success">Aceptado (Permiso)</span>
                                            @else
                                                <span class="badge bg-danger">Rechazado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No has enviado ningún justificante todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($misJustificantes->hasPages())
                        <div class="pt-3 mt-3 border-top d-flex justify-content-center">
                            {{ $misJustificantes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
