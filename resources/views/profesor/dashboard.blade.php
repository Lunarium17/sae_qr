<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .card-hover { transition: transform .15s ease, box-shadow .15s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.12) !important; }
        .card-hover:active { transform: scale(.98); }
        a.card-hover:focus-visible { outline: 3px solid rgba(13,110,253,.35); outline-offset: 2px; }
    </style>
</head>
<body class="bg-light">

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
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('profesor.dashboard') }}">SAE QR | Inicio</a>
            <div class="d-flex align-items-center gap-2">
                @if ($profesor->foto_perfil)
                    <img src="{{ asset('storage/' . $profesor->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,.5);">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 34px; height: 34px; background: hsl({{ $avHue }}, 65%, 45%); font-size: .85rem;">{{ mb_strtoupper(mb_substr($profesor->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('profesor.perfil') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
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
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center mb-4">
            <h3 class="fw-bold text-dark mb-1">Bienvenido, {{ $profesor->name }}</h3>
            <p class="text-muted small mb-3">
                {{ $profesor->email }}
            </p>

            @php
                $carrerasDocente = $grupos->pluck('carrera')->filter()->unique()->values();
            @endphp
            @if ($carrerasDocente->isNotEmpty())
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                    @foreach ($carrerasDocente as $c)
                        <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2" style="{{ \App\Support\CarreraColor::suave($c) }}">
                            <span class="d-inline-block rounded-circle flex-shrink-0" style="width: 8px; height: 8px; background: {{ \App\Support\CarreraColor::color($c) }};"></span>{{ $c }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('sesiones.index') }}" class="btn btn-primary rounded-3 px-4 py-2">
                    <i class="bi bi-qr-code-scan me-2"></i>Iniciar Sesión y Escanear
                </a>
                <a href="{{ route('grupos.index') }}" class="btn btn-outline-dark rounded-3 px-4 py-2">
                    <i class="bi bi-collection me-2"></i>Gestión de Grupos y Materias
                </a>
                <a href="{{ route('profesor.solicitudes') }}" class="btn btn-warning rounded-3 px-4 py-2">
                    <i class="bi bi-person-plus-fill me-2"></i>Solicitudes de Alumnos
                    @if($solicitudesPendientes > 0)
                        <span class="badge bg-danger ms-1">{{ $solicitudesPendientes }}</span>
                    @endif
                </a>
                <a href="{{ route('profesor.materias.importar') }}" class="btn btn-success rounded-3 px-4 py-2">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar Alumnos por CSV
                </a>
            </div>
        </div>

        <!-- 2. Tarjetas de KPIs Clicables en Tiempo Real -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('grupos.index') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-primary shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Grupos y Materias</h6>
                                <h3 class="fw-bold text-dark mb-0">{{ $gruposCount }} Grupos</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                                <i class="bi bi-collection fs-3"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="{{ route('profesor.solicitudes') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-info shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Matrícula Activa</h6>
                                <h3 class="fw-bold text-dark mb-0">{{ $alumnosAceptadosCount }} Inscritos</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                                <i class="bi bi-people-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="{{ route('sesiones.index') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-primary shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Clases Impartidas</h6>
                                <h3 class="fw-bold text-primary mb-0">{{ $totalSesiones }} Sesiones</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                                <i class="bi bi-easel fs-3"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="{{ route('profesor.estadisticas') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover border-0 border-start border-4 border-{{ $colorSemaforoGlobal }} shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1">Asistencia Global</h6>
                                <h3 class="fw-bold text-{{ $colorSemaforoGlobal }} mb-0">{{ $porcentajeAsistencia }}%</h3>
                            </div>
                            <div class="bg-{{ $colorSemaforoGlobal }} bg-opacity-10 text-{{ $colorSemaforoGlobal }} rounded-4 p-3">
                                <i class="bi bi-graph-up-arrow fs-3"></i>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-{{ $colorSemaforoGlobal }}" role="progressbar" data-width="{{ $porcentajeAsistencia }}"></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 3. Secciones del Panel -->
        <h5 class="fw-bold text-dark mb-3 px-1">Secciones</h5>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('sesiones.index') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                            <i class="bi bi-qr-code-scan fs-3"></i>
                        </div>
                        @if ($sesionesActivasCount > 0)
                            <span class="badge bg-success rounded-pill">Clase activa</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark">Sesiones y Escáner</h5>
                    <p class="text-muted small mb-2">Genera el QR de tu clase y registra los pases de tus alumnos en tiempo real.</p>
                    <span class="text-primary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('grupos.index') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-dark bg-opacity-10 text-dark rounded-4 p-3">
                            <i class="bi bi-collection fs-3"></i>
                        </div>
                        <span class="badge bg-dark rounded-pill">{{ $gruposCount }} grupos</span>
                    </div>
                    <h5 class="fw-bold text-dark">Grupos y Materias</h5>
                    <p class="text-muted small mb-2">Administra tus grupos oficiales universitarios y asigna las materias que impartes.</p>
                    <span class="text-dark small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.solicitudes') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3">
                            <i class="bi bi-person-plus-fill fs-3"></i>
                        </div>
                        @if ($solicitudesPendientes > 0)
                            <span class="badge bg-danger rounded-pill">{{ $solicitudesPendientes }} pendientes</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark">Solicitudes de Alumnos</h5>
                    <p class="text-muted small mb-2">Aprueba o rechaza las solicitudes de alta de alumnos en tus materias.</p>
                    <span class="text-warning small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.justificantes') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                            <i class="bi bi-file-earmark-text fs-3"></i>
                        </div>
                        @if ($justificantesPendientes > 0)
                            <span class="badge bg-warning text-dark rounded-pill">{{ $justificantesPendientes }} pendientes</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark">Justificantes</h5>
                    <p class="text-muted small mb-2">Revisa y valida los justificantes de ausencias que envían tus alumnos.</p>
                    <span class="text-info small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.reportes') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                            <i class="bi bi-bar-chart-line fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Reportes y Exportación</h5>
                    <p class="text-muted small mb-2">Consulta y descarga reportes de asistencia por materia en PDF o Excel.</p>
                    <span class="text-success small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.estadisticas') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                            <i class="bi bi-graph-up-arrow fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Estadísticas y Riesgo</h5>
                    <p class="text-muted small mb-2">Gráficas de incidencias y control del derecho a examen ordinario por alumno.</p>
                    <span class="text-secondary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.materias.importar') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                            <i class="bi bi-file-earmark-arrow-up fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Importar Alumnos por CSV</h5>
                    <p class="text-muted small mb-2">Inscribe a un grupo completo desde un archivo, con revisión previa antes de confirmar.</p>
                    <span class="text-success small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('profesor.perfil') }}" class="card card-hover border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                            <i class="bi bi-person-gear fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Mi Perfil</h5>
                    <p class="text-muted small mb-2">Actualiza tus datos de contacto, clave de docente y foto de perfil.</p>
                    <span class="text-secondary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short"></i></span>
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
