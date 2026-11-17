<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivo de Semestres - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    @php
        $admin = \Illuminate\Support\Facades\Auth::user();
        $avHue = abs(crc32($admin->name ?? 'U')) % 360;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Archivo de Semestres</a>
            <div class="d-flex align-items-center gap-2">
                @if ($admin->foto_perfil)
                    <img src="{{ asset('storage/' . $admin->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,.5);">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 34px; height: 34px; background: hsl({{ $avHue }}, 65%, 45%); font-size: .85rem;">{{ mb_strtoupper(mb_substr($admin->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Inicio
                </a>
                <a href="{{ route('admin.perfil') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                    Mi Perfil
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <!-- Semestres Vigentes: candidatos a cierre -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-hourglass-split me-2 text-primary"></i>Semestres en Curso</h5>
                <p class="text-muted small mb-3">Al cerrar un semestre, sus sesiones se archivan: los docentes inician el siguiente periodo desde cero y este historial queda guardado aquí para el administrador.</p>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Periodo</th>
                                <th class="text-center">Sesiones</th>
                                <th class="text-center">Registros</th>
                                <th class="text-center">Resumen (A / R / P / F)</th>
                                <th class="text-center">Sesiones Activas</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($semestresVigentes as $sem)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-2">
                                            <i class="bi bi-calendar-range me-1"></i>{{ $sem->periodo }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold">{{ $sem->sesiones }}</td>
                                    <td class="text-center">{{ $sem->registros }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success me-1">A: {{ $sem->asistencias }}</span>
                                        <span class="badge bg-warning text-dark me-1">R: {{ $sem->retardos }}</span>
                                        <span class="badge bg-info text-dark me-1">P: {{ $sem->permisos }}</span>
                                        <span class="badge bg-danger">F: {{ $sem->faltas }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($sem->activas > 0)
                                            <span class="badge bg-success">{{ $sem->activas }} activa(s)</span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.archivo.cerrar') }}" method="POST" onsubmit="return confirm('¿Cerrar el semestre {{ $sem->periodo }}? Las {{ $sem->sesiones }} sesiones se archivarán y los docentes iniciarán el siguiente semestre desde cero. Esta acción no borra la información: queda en el Archivo.');">
                                            @csrf
                                            <input type="hidden" name="periodo" value="{{ $sem->periodo }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 fw-semibold">
                                                <i class="bi bi-archive me-1"></i>Cerrar Semestre
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay sesiones vigentes todavía. Cuando los docentes impartan clases, aparecerán aquí por periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Semestres Archivados: historial conservado -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-archive me-2 text-secondary"></i>Archivo de Semestres Cerrados</h5>
                <p class="text-muted small mb-3">Historial completo conservado por el sistema. Descarga el detalle por semestre en formato Excel (CSV).</p>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Periodo</th>
                                <th class="text-center">Sesiones</th>
                                <th class="text-center">Registros</th>
                                <th class="text-center">Resumen (A / R / P / F)</th>
                                <th>Carreras Involucradas</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($semestresArchivados as $sem)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark rounded-pill px-3 py-2">
                                            <i class="bi bi-archive me-1"></i>{{ $sem->periodo }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold">{{ $sem->sesiones }}</td>
                                    <td class="text-center">{{ $sem->registros }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success me-1">A: {{ $sem->asistencias }}</span>
                                        <span class="badge bg-warning text-dark me-1">R: {{ $sem->retardos }}</span>
                                        <span class="badge bg-info text-dark me-1">P: {{ $sem->permisos }}</span>
                                        <span class="badge bg-danger">F: {{ $sem->faltas }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($sem->carreras as $carrera)
                                                <span class="badge rounded-pill" style="{{ \App\Support\CarreraColor::suave($carrera) }}">{{ $carrera }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.archivo.exportar', ['periodo' => $sem->periodo]) }}" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">
                                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Descargar CSV
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Aún no hay semestres cerrados. El archivo se llena cuando cierras un semestre desde la tabla superior.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
