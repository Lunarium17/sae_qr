<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
</head>
<body>

    @php
        $profesor = \Illuminate\Support\Facades\Auth::user();
        $avHue = abs(crc32($profesor->name ?? 'U')) % 360;
        $profesorId = \Illuminate\Support\Facades\Auth::id();

        $misMaterias = \App\Models\Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->get();
        $misMateriaIds = $misMaterias->pluck('id');
        $materiasCount = $misMaterias->count();

        $grupos = \App\Models\Grupo::with(['materias' => function ($q) use ($profesorId) {
            $q->where('profesor_id', $profesorId)->orWhereNull('profesor_id');
        }])->get();
        $gruposCount = $grupos->count();

        $totalSesiones = \App\Models\SesionClase::where('profesor_id', $profesorId)->vigente()->count();
        $sesionesActivasCount = \App\Models\SesionClase::where('profesor_id', $profesorId)->where('estado', 'activa')->vigente()->count();

        $alumnosAceptadosCount = $misMateriaIds->isNotEmpty()
            ? \App\Models\Matricula::whereIn('materia_id', $misMateriaIds)->where('estado', 'aceptado')->count()
            : 0;
        $solicitudesPendientes = $misMateriaIds->isNotEmpty()
            ? \App\Models\Matricula::whereIn('materia_id', $misMateriaIds)->where('estado', 'pendiente')->count()
            : 0;
        $justificantesPendientes = \App\Models\Justificante::where('estado_aprobacion', 'pendiente')
            ->whereHas('registroAsistencia.sesion', function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->where('archivado', false);
            })
            ->count();

        $asistenciasCount = \App\Models\RegistroAsistencia::where('estado_incidencia', 'asistencia')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId)->where('archivado', false))->count();
        $retardosCount    = \App\Models\RegistroAsistencia::where('estado_incidencia', 'retardo')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId)->where('archivado', false))->count();
        $permisosCount    = \App\Models\RegistroAsistencia::where('estado_incidencia', 'permiso')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId)->where('archivado', false))->count();
        $faltasCount      = \App\Models\RegistroAsistencia::where('estado_incidencia', 'falta')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId)->where('archivado', false))->count();

        $totalRegistros = $asistenciasCount + $retardosCount + $permisosCount + $faltasCount;
        $asistenciasEfectivas = $asistenciasCount + $retardosCount + $permisosCount;
        $porcentajeAsistencia = $totalRegistros > 0 ? round(($asistenciasEfectivas / $totalRegistros) * 100) : 0;

        $colorSemaforoGlobal = $porcentajeAsistencia >= 85 ? 'success' : ($porcentajeAsistencia >= 70 ? 'warning' : 'danger');

        // Saludo dinamico y datos del periodo activo
        $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
        $fechaHoy = ucfirst(now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y'));
        $periodosActivos = $misMaterias->pluck('grupo.periodo_lectivo')->filter()->unique()->values();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('profesor.dashboard') }}">SAE QR | Inicio</a>
            <div class="d-flex align-items-center gap-2">
                @if ($profesor->foto_perfil)
                    <img src="{{ asset('storage/' . $profesor->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid #E2E8F0;">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 34px; height: 34px; background: #EFF6FF; color: #2563EB; font-size: .75rem;">{{ mb_strtoupper(mb_substr($profesor->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('profesor.perfil') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    Mi Perfil
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
        <!-- 1. Panel Principal de Bienvenida y Acciones Rápidas -->
        <div class="card border-0 shadow-sm rounded-4 p-4 banner-institucional text-white mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h3 class="fw-bold mb-1">{{ $saludo }}, {{ $profesor->name }}</h3>
                    <p class="small mb-0 text-white-50">
                        <i class="bi bi-calendar3 me-1"></i>{{ $fechaHoy }}
                        @if ($periodosActivos->isNotEmpty())
                            <span class="mx-1">·</span>
                            <i class="bi bi-calendar-week me-1"></i>Periodo escolar {{ $periodosActivos->first() }}
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('sesiones.index') }}" class="btn btn-light rounded-3 px-4 py-2 fw-semibold" style="color: #19376d;">
                        <i class="bi bi-qr-code-scan me-2"></i>Iniciar Pase de Lista QR
                    </a>
                    <a href="{{ route('profesor.materias.importar') }}" class="btn btn-banner-translucido rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Métricas Bento: Protagonista + Indicadores -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6">
                <a href="{{ route('profesor.estadisticas') }}" class="d-block text-decoration-none h-100">
                    <div class="bento kpi-prota h-100 p-4 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="small fw-semibold" style="color: rgba(255,255,255,.65);">Asistencia Global</div>
                            <div class="numero-hero text-white mt-2">{{ $porcentajeAsistencia }}%</div>
                            <div class="progress rounded-pill mt-3" style="height: 10px; width: 220px; max-width: 100%;">
                                <div class="progress-bar" role="progressbar" data-width="{{ $porcentajeAsistencia }}" style="background: {{ ['success' => '#34D399', 'warning' => '#FBBF24', 'danger' => '#F87171'][$colorSemaforoGlobal] ?? '#34D399' }};"></div>
                            </div>
                            <div class="d-flex align-items-center gap-2 small mt-3" style="color: rgba(255,255,255,.65);">
                                <span class="punto-estado" style="background: #34D399;"></span>{{ $asistenciasEfectivas }} de {{ $totalRegistros }} registros efectivos
                            </div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 92px; height: 92px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.18);">
                            <i class="bi bi-graph-up-arrow fs-2"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="{{ route('grupos.index') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-cielo h-100 p-3 d-flex flex-column justify-content-between" style="min-height: 184px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="texto-slate small fw-semibold">Grupos</span>
                            <i class="bi bi-collection fs-6" style="color: #94A3B8;"></i>
                        </div>
                        <div class="numero-bento">{{ $gruposCount }}</div>
                        <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                            <span class="punto-estado" style="background: #2563EB;"></span>{{ $materiasCount }} materias
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="{{ route('profesor.solicitudes') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-esmeralda h-100 p-3 d-flex flex-column justify-content-between" style="min-height: 184px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="texto-slate small fw-semibold">Matrícula</span>
                            <i class="bi bi-people-fill fs-6" style="color: #94A3B8;"></i>
                        </div>
                        <div class="numero-bento">{{ $alumnosAceptadosCount }}</div>
                        @if ($solicitudesPendientes > 0)
                            <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                                <span class="punto-estado" style="background: #D97706;"></span>{{ $solicitudesPendientes }} solicitudes
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                                <span class="punto-estado" style="background: #059669;"></span>Solicitudes al día
                            </div>
                        @endif
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="{{ route('sesiones.index') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-indigo h-100 p-3 d-flex flex-column justify-content-between" style="min-height: 184px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="texto-slate small fw-semibold">Clases</span>
                            <i class="bi bi-easel fs-6" style="color: #94A3B8;"></i>
                        </div>
                        <div class="numero-bento">{{ $totalSesiones }}</div>
                        <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                            <span class="punto-estado" style="background: {{ $sesionesActivasCount > 0 ? '#059669' : '#6366F1' }};"></span>{{ $sesionesActivasCount }} activa{{ $sesionesActivasCount === 1 ? '' : 's' }} ahora
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 3. Secciones del Panel -->
        <div class="d-flex align-items-center gap-3 mb-4 px-1">
            <h5 class="titulo-panel mb-0">Secciones</h5>
            <div class="hr-divisor flex-grow-1"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('sesiones.index') }}" class="card tile-card tile-primaria shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                            <i class="bi bi-qr-code-scan fs-3"></i>
                        </div>
                        @if ($sesionesActivasCount > 0)
                            <span class="badge bg-success rounded-pill">Clase activa</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark">Sesiones y Escáner</h5>
                    <p class="texto-slate small mb-2">Genera el QR de tu clase y registra los pases de tus alumnos en tiempo real.</p>
                    <span class="text-primary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('grupos.index') }}" class="card tile-card tile-gris shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-dark bg-opacity-10 text-dark rounded-4 p-3">
                            <i class="bi bi-collection fs-3"></i>
                        </div>
                        <span class="badge badge-estado badge-permiso rounded-pill">{{ $gruposCount }} grupos</span>
                    </div>
                    <h5 class="fw-bold text-dark">Grupos y Materias</h5>
                    <p class="texto-slate small mb-2">Administra tus grupos oficiales universitarios y asigna las materias que impartes.</p>
                    <span class="text-dark small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.solicitudes') }}" class="card tile-card tile-ambar shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3">
                            <i class="bi bi-person-plus-fill fs-3"></i>
                        </div>
                        @if ($solicitudesPendientes > 0)
                            <span class="badge bg-danger rounded-pill">{{ $solicitudesPendientes }} pendientes</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark">Solicitudes de Alumnos</h5>
                    <p class="texto-slate small mb-2">Aprueba o rechaza las solicitudes de alta de alumnos en tus materias.</p>
                    <span class="text-warning small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.justificantes') }}" class="card tile-card tile-cian shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                            <i class="bi bi-file-earmark-text fs-3"></i>
                        </div>
                        @if ($justificantesPendientes > 0)
                            <span class="badge badge-estado badge-retardo rounded-pill">{{ $justificantesPendientes }} pendientes</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark">Justificantes</h5>
                    <p class="texto-slate small mb-2">Revisa y valida los justificantes de ausencias que envían tus alumnos.</p>
                    <span class="text-info small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.reportes') }}" class="card tile-card tile-verde shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                            <i class="bi bi-bar-chart-line fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Reportes y Exportación</h5>
                    <p class="texto-slate small mb-2">Consulta y descarga reportes de asistencia por materia en PDF o Excel.</p>
                    <span class="text-success small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.estadisticas') }}" class="card tile-card tile-indigo shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                            <i class="bi bi-graph-up-arrow fs-3"></i>
                        </div>
                        <span class="badge badge-estado rounded-pill {{ ['success' => 'badge-suave-verde', 'warning' => 'badge-retardo', 'danger' => 'badge-falta'][$colorSemaforoGlobal] ?? 'neutra-slate' }}">
                            {{ $porcentajeAsistencia }}% asistencia
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark">Estadísticas y Riesgo</h5>
                    <p class="texto-slate small mb-2">Gráficas de incidencias y control del derecho a examen ordinario por alumno.</p>
                    <span class="text-secondary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-width]').forEach(function (el) {
                el.style.width = el.getAttribute('data-width') + '%';
            });
        });
    </script>
</body>
</html>
