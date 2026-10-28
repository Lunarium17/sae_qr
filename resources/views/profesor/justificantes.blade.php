<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Justificantes | SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}">
                <i class="bi bi-arrow-left-circle me-2"></i>Volver al Panel
            </a>
            <span class="navbar-text text-white fw-bold">Revisión de Permisos y Justificantes</span>
        </div>
    </nav>

    <div class="container my-5">
        @if(session('success'))
            <div class="alert alert-success rounded-3 shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h4 class="fw-bold text-dark mb-4"><i class="bi bi-envelope-paper me-2 text-info"></i>Comprobantes Recibidos</h4>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Materia</th>
                            <th>Fecha Sesión</th>
                            <th>Motivo del Alumno</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th style="min-width: 280px;">Comentario Docente (Opcional) y Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($justificantes as $just)
                            <tr>
                                <td class="fw-semibold">{{ $just->registroAsistencia->alumno->name }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $just->registroAsistencia->sesion->materia->codigo_materia }}</span>
                                    {{ $just->registroAsistencia->sesion->materia->nombre_materia }}
                                </td>
                                <td>{{ $just->registroAsistencia->sesion->fecha }}</td>
                                <td class="small">{{ $just->motivo }}</td>
                                <td>
                                    @if($just->documento_url)
                                        <a href="{{ asset('storage/' . $just->documento_url) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Abrir Archivo
                                        </a>
                                    @else
                                        <span class="text-muted small">Sin archivo adjunto</span>
                                    @endif
                                </td>
                                <td>
                                    @if($just->estado_aprobacion === 'pendiente')
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    @elseif($just->estado_aprobacion === 'aceptado')
                                        <span class="badge bg-success">Aceptado</span>
                                    @else
                                        <span class="badge bg-danger">Rechazado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($just->estado_aprobacion === 'pendiente')
                                        <form action="{{ route('profesor.justificantes.estado', $just->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-2">
                                                <input type="text" name="comentario_profesor" class="form-control form-control-sm rounded-3" maxlength="500" placeholder="Observación opcional (ej. Sello legible / Falta firma)...">
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" name="estado_aprobacion" value="aceptado" class="btn btn-sm btn-success rounded-3 flex-fill">
                                                    <i class="bi bi-check-lg me-1"></i>Aprobar
                                                </button>
                                                <button type="submit" name="estado_aprobacion" value="rechazado" class="btn btn-sm btn-outline-danger rounded-3 flex-fill">
                                                    <i class="bi bi-x-lg me-1"></i>Rechazar
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="small">
                                            <span class="text-muted fw-semibold">Procesado</span>
                                            @if($just->comentario_profesor)
                                                <div class="text-secondary fst-italic mt-1">
                                                    <i class="bi bi-chat-left-text me-1"></i>"{{ $just->comentario_profesor }}"
                                                </div>
                                            @else
                                                <div class="text-muted">Sin comentarios del docente</div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No hay justificantes registrados por el momento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
