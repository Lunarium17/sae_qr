<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.panel-css')
    <style>
        body { background: #EEF2F6; }

        .navbar-editorial { background: #FFFFFF; border-bottom: 1px solid #E2E8F0; }
        #jsCampanaBtn { color: #334155; border-color: #CBD5E1; }
        #jsCampanaBtn:hover, #jsCampanaBtn:focus { background: #F1F5F9; color: #0F172A; }

        .badge-suave-verde { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .badge-suave-carmesi { background: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3; }
        .texto-slate { color: #334155; }

        .bento {
            background: #FFFFFF;
            border: 1px solid rgba(203, 213, 225, .9);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -2px rgba(15, 23, 42, .07), 0 12px 24px -10px rgba(15, 23, 42, .12);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .bento:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 10px -4px rgba(15, 23, 42, .10), 0 20px 32px -12px rgba(15, 23, 42, .18);
        }
        .metrica-prota { background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); border: none; color: #FFFFFF; }
        .metrica-ambar { background: linear-gradient(180deg, #FFFBEB 0%, #FFFFFF 55%); border-top: 3px solid #D97706; }
        .metrica-coral { background: linear-gradient(180deg, #FFF1F2 0%, #FFFFFF 55%); border-top: 3px solid #E11D48; }
        .metrica-cielo { background: linear-gradient(180deg, #EFF6FF 0%, #FFFFFF 55%); border-top: 3px solid #2563EB; }
        .numero-hero { font-size: 2.75rem; font-weight: 800; line-height: 1; letter-spacing: -.02em; }
        .numero-bento { font-size: 3rem; font-weight: 800; color: #0F172A; line-height: 1; letter-spacing: -.02em; }
        .punto-estado { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

        .bento-azul { background: #EFF6FF; border-color: #BFDBFE; }
        .bento-indigo { background: #EEF2FF; border-color: #C7D2FE; }
        .bento-esmeralda { background: #ECFDF5; border-color: #A7F3D0; }
        .bento-marcada { background: #FFFFFF; border-color: #94A3B8; }

        .pildora-materia { font-size: .92rem; }
        .pildora-azul { background: #EFF6FF; border: 1px solid #BFDBFE; }
        .pildora-esmeralda { background: #ECFDF5; border: 1px solid #A7F3D0; }
        .pildora-ambar { background: #FEF3C7; border: 1px solid #FCD34D; }
        .pildora-coral { background: #FFF1F2; border: 1px solid #FECDD3; }

        .hr-divisor { border-top: 1px solid #CBD5E1; opacity: 1; margin: 0; }

        .hoja-calendario {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 1rem;
            overflow: hidden;
            width: 76px;
            box-shadow: 0 4px 12px -6px rgba(15, 23, 42, .18);
        }
        .flecha-circular {
            width: 44px; height: 44px;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            display: flex; align-items: center; justify-content: center;
            transition: background .18s ease, border-color .18s ease;
        }
        .flecha-circular i { color: #64748B; transition: transform .18s ease, color .18s ease; }
        .bento:hover .flecha-circular { background: #2563EB; border-color: #2563EB; }
        .bento:hover .flecha-circular i { color: #FFFFFF; transform: rotate(-45deg); }
    </style>
</head>
<body style="background: #E2E8F0;">

    @php
        $alumno = \Illuminate\Support\Facades\Auth::user();
        $userId = \Illuminate\Support\Facades\Auth::id();

        $pendientesCount  = \App\Models\Matricula::where('user_id', $userId)->where('estado', 'pendiente')->count();
        $asistenciasCount = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'asistencia')->count();
        $retardosCount    = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'retardo')->count();
        $faltasCount      = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'falta')->count();
        $permisosCount    = \App\Models\RegistroAsistencia::where('user_id', $userId)->where('estado_incidencia', 'permiso')->count();

        // Materias en las que el alumno está aceptado
        $matriculasAceptadas = \App\Models\Matricula::with(['grupo', 'materia'])
            ->where('user_id', $userId)
            ->where('estado', 'aceptado')
            ->get();

        $materiaIdsAceptadas = $matriculasAceptadas->pluck('materia_id')->filter()->toArray();

        // Detectar si existe alguna sesión activa en curso para sus materias donde aún no haya escaneado su QR
        $sesionesActivasPendientes = collect();
        if (!empty($materiaIdsAceptadas)) {
            $sesionesActivasPendientes = \App\Models\SesionClase::with('materia.grupo')
                ->whereIn('materia_id', $materiaIdsAceptadas)
                ->where('estado', 'activa')
                ->whereDoesntHave('asistencias', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->get();
        }

        // Faltas y retardos que aún NO tienen un justificante enviado o cuyo justificante fue rechazado
        $incidenciasPorJustificar = \App\Models\RegistroAsistencia::with(['sesion.materia', 'justificante'])
            ->where('user_id', $userId)
            ->whereIn('estado_incidencia', ['falta', 'retardo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($reg) {
                return !$reg->justificante || $reg->justificante->estado_aprobacion === 'rechazado';
            });

        $avHue = abs(crc32($alumno->name ?? 'U')) % 360;

        // Saludo dinamico y datos del periodo activo
        $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
        $fechaHoy = ucfirst(now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y'));
        $periodosActivos = $matriculasAceptadas->pluck('grupo.periodo_lectivo')->filter()->unique()->values();

        // Datos para el Bento Grid
        $totalRegistrosAlumno = $asistenciasCount + $retardosCount + $permisosCount + $faltasCount;
        $porcentajeAlumno = $totalRegistrosAlumno > 0 ? round((($asistenciasCount + $retardosCount + $permisosCount) / $totalRegistrosAlumno) * 100) : 0;
        $circunferenciaAnillo = round(2 * pi() * 52);
        $trazoAnillo = round($circunferenciaAnillo * $porcentajeAlumno / 100);

        $inicialesAlumno = mb_strtoupper(implode('', array_map(fn ($palabra) => mb_substr($palabra, 0, 1), array_slice(preg_split('/\s+/', trim($alumno->name ?? 'U')), 0, 2))));

        $siglasMaterias = $matriculasAceptadas->take(2)->map(function ($m) {
            $palabras = array_values(array_filter(preg_split('/\s+/', trim($m->materia->nombre_materia ?? '')), fn ($p) => $p !== ''));
            $letras = '';
            foreach (array_slice($palabras, 0, 2) as $palabra) {
                $letras .= mb_substr($palabra, 0, 1);
            }
            return [
                'sigla'  => mb_strtoupper($letras),
                'nombre' => implode(' ', array_slice($palabras, 0, 3)),
            ];
        })->values();

        $mesHoy = mb_strtoupper(now()->locale('es')->translatedFormat('M'));
        $diaHoy = now()->day;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="#">SAE QR | Inicio</a>
            <div class="d-flex align-items-center gap-2">
                @if ($alumno->foto_perfil)
                    <img src="{{ asset('storage/' . $alumno->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid #E2E8F0;">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 34px; height: 34px; background: #EFF6FF; color: #2563EB; font-size: .75rem;">{{ $inicialesAlumno }}</span>
                @endif
                <a href="{{ route('alumno.perfil') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
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
        <!-- 1. Banner de Bienvenida y Acciones -->
        <div class="card border-0 shadow-sm rounded-4 p-4 banner-institucional text-white mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h3 class="fw-bold mb-0">{{ $saludo }}, {{ $alumno->name }}</h3>
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,.16);">
                            {{ $alumno->matricula_codigo ? 'Matrícula: ' . $alumno->matricula_codigo : 'Sin matrícula registrada' }}
                        </span>
                    </div>
                    <p class="small mb-0 text-white-50">
                        <i class="bi bi-calendar3 me-1"></i>{{ $fechaHoy }}
                        @if ($periodosActivos->isNotEmpty())
                            <span class="mx-1">·</span>
                            <i class="bi bi-calendar-week me-1"></i>Periodo escolar {{ $periodosActivos->first() }}
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('alumno.qr') }}" class="btn btn-light rounded-3 px-4 py-2 fw-semibold" style="color: #19376d;">
                        <i class="bi bi-qr-code me-2"></i>Mi Código QR
                    </a>
                    <a href="{{ route('alumno.solicitar') }}" class="btn btn-banner-translucido rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-search me-2"></i>Buscar y Solicitar Grupo
                        @if ($pendientesCount > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ $pendientesCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Métricas Bento -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-prota h-100 p-4 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="small fw-semibold" style="color: rgba(255,255,255,.65);">Asistencias</div>
                            <div class="numero-hero text-white mt-2">{{ $asistenciasCount }}</div>
                            <div class="d-flex align-items-center gap-2 small mt-3" style="color: rgba(255,255,255,.65);">
                                <span class="punto-estado" style="background: #34D399;"></span>{{ $porcentajeAlumno }}% de asistencia efectiva
                            </div>
                        </div>
                        <svg viewBox="0 0 120 120" style="width: 118px; height: 118px; flex-shrink: 0;" role="img" aria-label="{{ $porcentajeAlumno }}% de asistencia efectiva">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.14)" stroke-width="10"></circle>
                            <circle cx="60" cy="60" r="52" fill="none" stroke="#34D399" stroke-width="10" stroke-linecap="round" stroke-dasharray="{{ $trazoAnillo }} {{ $circunferenciaAnillo }}" transform="rotate(-90 60 60)"></circle>
                            <text x="60" y="67" text-anchor="middle" fill="#FFFFFF" font-size="22" font-weight="700">{{ $porcentajeAlumno }}%</text>
                        </svg>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-ambar h-100 p-3 d-flex flex-column justify-content-between" style="min-height: 184px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="texto-slate small fw-semibold">Retardos</span>
                            <i class="bi bi-clock fs-6" style="color: #94A3B8;"></i>
                        </div>
                        <div class="numero-bento">{{ $retardosCount }}</div>
                        <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                            <span class="punto-estado" style="background: #D97706;"></span>Cada 3 retardos = 1 falta
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="{{ route('alumno.justificantes') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-coral h-100 p-3 d-flex flex-column justify-content-between" style="min-height: 184px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="texto-slate small fw-semibold">Faltas</span>
                            <i class="bi bi-x-circle fs-6" style="color: #94A3B8;"></i>
                        </div>
                        <div class="numero-bento">{{ $faltasCount }}</div>
                        @if ($incidenciasPorJustificar->isEmpty())
                            <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                                <span class="punto-estado" style="background: #059669;"></span>Todo justificado
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                                <span class="punto-estado" style="background: #E11D48;"></span>{{ $incidenciasPorJustificar->count() }} por justificar
                            </div>
                        @endif
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none h-100">
                    <div class="bento metrica-cielo h-100 p-3 d-flex flex-column justify-content-between" style="min-height: 184px;">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="texto-slate small fw-semibold">Permisos</span>
                            <i class="bi bi-file-earmark-check fs-6" style="color: #94A3B8;"></i>
                        </div>
                        <div class="numero-bento">{{ $permisosCount }}</div>
                        <div class="d-flex align-items-center gap-2 texto-slate" style="font-size: .72rem;">
                            <span class="punto-estado" style="background: #2563EB;"></span>Justificados
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- 3. Secciones Bento 2x2 -->
        <div class="d-flex align-items-center gap-3 mb-4 px-1">
            <h5 class="fw-bold text-dark mb-0" style="color: #0F172A;">Secciones</h5>
            <div class="hr-divisor flex-grow-1"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <a href="{{ route('alumno.qr') }}" class="d-block text-decoration-none h-100">
                    <div class="bento bento-azul h-100 p-4 position-relative" style="min-height: 216px;">
                        @if ($sesionesActivasPendientes->count() > 0)
                            <span class="badge bg-success position-absolute rounded-pill" style="top: 1rem; right: 1rem;">Clase activa</span>
                        @endif
                        <div style="max-width: 65%;">
                            <h6 class="fw-bold text-dark mb-1" style="color: #0F172A;">Mi Código QR</h6>
                            <p class="texto-slate small mb-0">Preséntalo en tu clase en curso para registrar tu asistencia.</p>
                        </div>
                        <svg viewBox="0 0 100 100" class="position-absolute" style="width: 150px; height: 150px; right: -22px; bottom: -22px; transform: rotate(6deg);" aria-hidden="true">
                            <rect width="100" height="100" rx="10" fill="#EFF6FF"></rect>
                            <rect x="10" y="10" width="24" height="24" rx="4" fill="none" stroke="#2563EB" stroke-width="6"></rect>
                            <rect x="17" y="17" width="10" height="10" fill="#2563EB"></rect>
                            <rect x="66" y="10" width="24" height="24" rx="4" fill="none" stroke="#2563EB" stroke-width="6"></rect>
                            <rect x="73" y="17" width="10" height="10" fill="#2563EB"></rect>
                            <rect x="10" y="66" width="24" height="24" rx="4" fill="none" stroke="#2563EB" stroke-width="6"></rect>
                            <rect x="17" y="73" width="10" height="10" fill="#2563EB"></rect>
                            <rect x="44" y="10" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="56" y="18" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="44" y="30" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="46" y="46" width="12" height="12" fill="#2563EB"></rect>
                            <rect x="62" y="46" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="74" y="54" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="46" y="62" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="58" y="70" width="12" height="8" fill="#2563EB"></rect>
                            <rect x="74" y="70" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="46" y="82" width="8" height="8" fill="#2563EB"></rect>
                            <rect x="62" y="86" width="8" height="8" fill="#2563EB"></rect>
                        </svg>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('alumno.materias') }}" class="d-block text-decoration-none h-100">
                    <div class="bento bento-indigo h-100 p-4 position-relative" style="min-height: 216px;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-1" style="color: #0F172A;">Mis Materias</h6>
                                <p class="texto-slate small mb-0">Consulta tu registro de asistencia y derecho a examen ordinario.</p>
                            </div>
                            <span class="badge bg-primary rounded-pill px-3 py-2 fs-6 flex-shrink-0">{{ $matriculasAceptadas->count() }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @forelse ($siglasMaterias as $i => $materiaBento)
                                <span class="d-inline-flex align-items-center gap-2 pildora-materia pildora-{{ ['azul', 'esmeralda', 'ambar', 'coral'][$i % 4] }} rounded-3 px-3 py-2">
                                    <span class="punto-estado" style="background: {{ ['#2563EB', '#059669', '#D97706', '#E11D48'][$i % 4] }};"></span>
                                    <span class="fw-bold text-dark" style="color: #0F172A;">{{ $materiaBento['sigla'] }}</span>
                                    <span class="nombre-pill">· {{ $materiaBento['nombre'] }}</span>
                                </span>
                            @empty
                                <span class="texto-slate" style="font-size: .72rem;">Aún no tienes materias inscritas.</span>
                            @endforelse
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('alumno.calendario') }}" class="d-block text-decoration-none h-100">
                    <div class="bento bento-esmeralda h-100 p-4 d-flex justify-content-between align-items-center gap-3" style="min-height: 216px;">
                        <div>
                            <h6 class="fw-bold text-dark mb-1" style="color: #0F172A;">Calendario</h6>
                            <p class="texto-slate small mb-0">Historial de asistencias, retardos y faltas del mes actual.</p>
                        </div>
                        <div class="hoja-calendario flex-shrink-0" style="height: 84px;">
                            <div class="text-center fw-bold text-white" style="background: #2563EB; font-size: .78rem; padding: .32rem 0;">{{ $mesHoy }}</div>
                            <div class="text-center" style="font-size: 2.1rem; font-weight: 800; color: #0F172A; line-height: 1.35;">{{ $diaHoy }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('alumno.justificantes') }}" class="d-block text-decoration-none h-100">
                    <div class="bento bento-marcada h-100 p-4 d-flex justify-content-between align-items-center gap-3" style="min-height: 216px;">
                        <div>
                            <h6 class="fw-bold text-dark mb-1" style="color: #0F172A;">Justificantes</h6>
                            <p class="texto-slate small mb-0">Sube tu comprobante para justificar faltas y retardos ante el docente.</p>
                            <div class="mt-3">
                                @if ($incidenciasPorJustificar->isEmpty())
                                    <span class="badge badge-suave-verde rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                                        <span class="punto-estado" style="background: #059669;"></span>Todo al corriente
                                    </span>
                                @else
                                    <span class="badge badge-suave-carmesi rounded-pill px-3 py-2">{{ $incidenciasPorJustificar->count() }} por justificar</span>
                                @endif
                            </div>
                        </div>
                        <div class="flecha-circular rounded-circle flex-shrink-0">
                            <i class="bi bi-arrow-right fs-5"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
