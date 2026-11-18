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
        $admin = \Illuminate\Support\Facades\Auth::user();
        $avHue = abs(crc32($admin->name ?? 'U')) % 360;

        // Saludo dinamico y periodo institucional
        $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
        $fechaHoy = ucfirst(now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y'));
        $periodosActivos = \App\Models\Grupo::whereNotNull('periodo_lectivo')->distinct()->pluck('periodo_lectivo')->values();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="{{ route('admin.dashboard') }}">
                SAE QR | Inicio
            </a>
            <div class="d-flex align-items-center gap-2">
                @if ($admin->foto_perfil)
                    <img src="{{ asset('storage/' . $admin->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid #E2E8F0;">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 34px; height: 34px; background: #EFF6FF; color: #2563EB; font-size: .75rem;">{{ mb_strtoupper(mb_substr($admin->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('admin.perfil') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4" role="alert">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <!-- 1. Bienvenida y Acciones Rápidas -->
        <div class="card border-0 shadow-sm rounded-4 p-4 banner-institucional text-white mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h3 class="fw-bold mb-1">{{ $saludo }}, {{ $admin->name }}</h3>
                    <p class="small mb-0 text-white-50">
                        <i class="bi bi-calendar3 me-1"></i>{{ $fechaHoy }}
                        @if ($periodosActivos->isNotEmpty())
                            <span class="mx-1">·</span>
                            <i class="bi bi-calendar-week me-1"></i>Periodo escolar {{ $periodosActivos->first() }}
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.codigos') }}" class="btn btn-light rounded-3 px-4 py-2 fw-semibold" style="color: #19376d;">
                        <i class="bi bi-key-fill me-2"></i>Gestionar Códigos de Acceso
                    </a>
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-banner-translucido rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-people-fill me-2"></i>Directorio de Usuarios
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Tarjetas de Indicadores Globales (KPIs) Clicables -->
        <div class="row g-4 mb-5">
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('admin.usuarios', ['rol' => 'alumno']) }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover kpi-azul rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-4 p-3 me-3 text-white" style="background: #2563eb;">
                                <i class="bi bi-mortarboard-fill fs-3"></i>
                            </div>
                            <div>
                                <div class="texto-slate small fw-semibold">Total Alumnos</div>
                                <h3 class="fw-bold mb-0" style="color: #0F172A;">{{ $totalAlumnos }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('admin.usuarios', ['rol' => 'profesor']) }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover kpi-esmeralda rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-4 p-3 me-3 text-white" style="background: #059669;">
                                <i class="bi bi-person-workspace fs-3"></i>
                            </div>
                            <div>
                                <div class="texto-slate small fw-semibold">Total Profesores</div>
                                <h3 class="fw-bold mb-0" style="color: #0F172A;">{{ $totalProfesores }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('admin.codigos') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover kpi-ambar rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-4 p-3 me-3 text-white" style="background: #d97706;">
                                <i class="bi bi-key-fill fs-3"></i>
                            </div>
                            <div>
                                <div class="texto-slate small fw-semibold">Códigos Docentes</div>
                                <h3 class="fw-bold mb-0" style="color: #0F172A;">{{ $codigosDisponiblesCount }} <span class="fs-6 fw-normal texto-slate">disponibles</span></h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('admin.grupos') }}" class="d-block text-decoration-none text-reset h-100">
                    <div class="card card-hover kpi-indigo rounded-4 h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-4 p-3 me-3 text-white" style="background: #6366f1;">
                                <i class="bi bi-collection-fill fs-3"></i>
                            </div>
                            <div>
                                <div class="texto-slate small fw-semibold">Grupos y Materias</div>
                                <h3 class="fw-bold mb-0" style="color: #0F172A;">{{ $totalGrupos }} / {{ $totalMaterias }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 3. Tarjeta Protagonista: Asistencia Global Institucional -->
        <div class="card kpi-prota rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-white">
                            Índice Global de Asistencia Escolar
                            <span class="info-icon" tabindex="0" role="button" aria-label="Promedio de asistencia institucional ({{ $totalRegistros }} pases de lista)." style="color: #93C5FD;">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">Promedio de asistencia institucional sobre {{ $totalRegistros }} pases de lista.</span>
                            </span>
                        </h6>
                    </div>
                    <span class="badge rounded-pill fs-6 px-3 py-2" style="background: rgba(255,255,255,.16); color: #FFFFFF;">
                        {{ $porcentajeAsistencia }}% Asistencia Efectiva
                    </span>
                </div>

                <div class="progress rounded-pill mb-3" style="height: 14px;">
                    <div class="progress-bar js-barra-progreso" role="progressbar" data-porcentaje="{{ $porcentajeAsistencia }}" aria-valuenow="{{ $porcentajeAsistencia }}" aria-valuemin="0" aria-valuemax="100" style="width: 0; background: {{ ['success' => '#34D399', 'warning' => '#FBBF24', 'danger' => '#F87171'][$colorSemaforoGlobal] ?? '#34D399' }};"></div>
                </div>

                <div class="row text-center g-2 small">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 fw-semibold text-white" style="background: rgba(255,255,255,.10);">
                            Asistencias: {{ $asistenciasCount }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 fw-semibold text-white" style="background: rgba(255,255,255,.10);">
                            Retardos: {{ $retardosCount }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 fw-semibold text-white" style="background: rgba(255,255,255,.10);">
                            Permisos / Justificados: {{ $permisosCount }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded-3 fw-semibold text-white" style="background: rgba(255,255,255,.10);">
                            Faltas: {{ $faltasCount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Secciones del Panel -->
        <div class="d-flex align-items-center gap-3 mb-4 px-1">
            <h5 class="titulo-panel mb-0">Secciones</h5>
            <div class="hr-divisor flex-grow-1"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.codigos') }}" class="card tile-card tile-ambar shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3">
                            <i class="bi bi-key-fill fs-3"></i>
                        </div>
                        <span class="badge badge-estado badge-suave-verde rounded-pill">{{ $codigosDisponiblesCount }} disponibles</span>
                    </div>
                    <h5 class="fw-bold text-dark">Códigos de Acceso Docente</h5>
                    <p class="texto-slate small mb-2">Genera y administra códigos de un solo uso para registrar profesores.</p>
                    <span class="text-warning small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.grupos') }}" class="card tile-card tile-cian shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                            <i class="bi bi-collection-fill fs-3"></i>
                        </div>
                        <span class="badge badge-estado badge-permiso rounded-pill">{{ $totalGrupos }} grupos</span>
                    </div>
                    <h5 class="fw-bold text-dark">Catálogo de Grupos Oficiales</h5>
                    <p class="texto-slate small mb-2">Crea, edita o elimina los grupos oficiales por carrera, semestre y turno.</p>
                    <span class="text-info small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.usuarios') }}" class="card tile-card tile-gris shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-dark bg-opacity-10 text-dark rounded-4 p-3">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <span class="badge badge-estado neutra-slate rounded-pill">{{ $totalUsuarios }} cuentas</span>
                    </div>
                    <h5 class="fw-bold text-dark">Directorio de Usuarios</h5>
                    <p class="texto-slate small mb-2">Gestiona alumnos, profesores y administradores, y restablece contraseñas.</p>
                    <span class="text-dark small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>

            <div class="col-md-6 col-lg-4">
                <a href="{{ route('admin.archivo') }}" class="card tile-card tile-indigo shadow-sm rounded-4 p-4 bg-white h-100 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                            <i class="bi bi-archive-fill fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Archivo de Semestres</h5>
                    <p class="texto-slate small mb-2">Cierra semestres terminados y consulta o descarga el historial completo de asistencias.</p>
                    <span class="text-secondary small fw-semibold mt-auto"><i class="bi bi-arrow-right-short tile-flecha"></i></span>
                </a>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-barra-progreso').forEach(function (barra) {
                const pct = barra.getAttribute('data-porcentaje') || 0;
                barra.style.width = pct + '%';
            });
        });
    </script>
</body>
</html>
