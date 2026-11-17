<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Materias Inscritas - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    @php
        $alumno = \Illuminate\Support\Facades\Auth::user();
        $avHue = abs(crc32($alumno->name ?? 'U')) % 360;
        $userId = \Illuminate\Support\Facades\Auth::id();

        // Materias en las que el alumno está aceptado
        $matriculasAceptadas = \App\Models\Matricula::with(['grupo', 'materia'])
            ->where('user_id', $userId)
            ->where('estado', 'aceptado')
            ->get();

        // Estadísticas por cada materia inscrita (solo sesiones del semestre vigente)
        $registrosAlumno = \App\Models\RegistroAsistencia::with('sesion')
            ->where('user_id', $userId)
            ->whereHas('sesion', fn($q) => $q->where('archivado', false))
            ->get();

        foreach ($matriculasAceptadas as $mat) {
            if (!$mat->materia) continue;
            $regsMat = $registrosAlumno->filter(fn($r) => $r->sesion && (int) $r->sesion->materia_id === (int) $mat->materia_id);
            $totMat  = $regsMat->count();
            $asistM  = $regsMat->where('estado_incidencia', 'asistencia')->count();
            $retM    = $regsMat->where('estado_incidencia', 'retardo')->count();
            $permM   = $regsMat->where('estado_incidencia', 'permiso')->count();
            $faltM   = $regsMat->where('estado_incidencia', 'falta')->count();
            $efecM   = $asistM + $retM + $permM;

            // Regla Universitaria: Cada 3 retardos acumulados en una misma asignatura equivalen a 1 falta
            $faltasPorRetM  = intdiv($retM, 3);
            $faltasTotalesM = $faltM + $faltasPorRetM;

            $mat->asistencias_mat     = $asistM;
            $mat->retardos_mat        = $retM;
            $mat->permisos_mat        = $permM;
            $mat->faltas_mat          = $faltM;
            $mat->faltas_retardos_mat = $faltasPorRetM;
            $mat->faltas_totales_mat  = $faltasTotalesM;
            $mat->total_sesiones      = $totMat;
            $mat->porcentaje_mat      = $totMat > 0 ? round(($efecM / $totMat) * 100) : null;
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Mis Materias</a>
            <div class="d-flex align-items-center gap-2">
                @if ($alumno->foto_perfil)
                    <img src="{{ asset('storage/' . $alumno->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,.5);">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 34px; height: 34px; background: hsl({{ $avHue }}, 65%, 45%); font-size: .85rem;">{{ mb_strtoupper(mb_substr($alumno->name ?? 'U', 0, 1)) }}</span>
                @endif
                <a href="{{ route('alumno.dashboard') }}" class="btn btn-light btn-sm text-primary fw-semibold rounded-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Inicio
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
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Mis Materias Inscritas y Derecho a Examen Ordinario</h5>
                    <div class="text-muted small">3 retardos = 1 falta · máx. 3 faltas.</div>
                </div>
                <span class="badge bg-primary rounded-pill">{{ $matriculasAceptadas->count() }} Materias Aceptadas</span>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Grupo / Carrera</th>
                            <th>Materia</th>
                            <th class="text-center">Registro (A / R / P / F)</th>
                            <th style="min-width: 155px;">% Asistencia</th>
                            <th class="text-center">Faltas Computadas</th>
                            <th class="text-center">Derecho a Examen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matriculasAceptadas as $mat)
                            @php
                                $pctM = $mat->porcentaje_mat;
                                $colorM = is_null($pctM) ? 'secondary' : ($pctM >= 85 ? 'success' : ($pctM >= 70 ? 'warning' : 'danger'));
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge rounded-pill" style="{{ \App\Support\CarreraColor::suave($mat->grupo->carrera ?? '') }}">
                                        {{ $mat->grupo->nombre_grupo ?? '-' }} ({{ $mat->grupo->turno ?? '-' }})
                                    </span>
                                    @if (!empty($mat->grupo->carrera) || !empty($mat->grupo->grado))
                                        <div class="small mt-1 d-flex align-items-start gap-1">
                                            @if ($mat->grupo->carrera)
                                                <span class="d-inline-block rounded-circle flex-shrink-0 mt-1" style="width: 8px; height: 8px; background: {{ \App\Support\CarreraColor::color($mat->grupo->carrera) }};"></span>
                                                <span class="fw-semibold" style="color: {{ \App\Support\CarreraColor::color($mat->grupo->carrera) }}; font-size: .75rem; line-height: 1.25;">{{ $mat->grupo->carrera }}</span>
                                            @endif
                                            @if (!empty($mat->grupo->grado))
                                                <span class="text-muted ms-1">{{ $mat->grupo->grado }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($mat->materia)
                                        <span class="badge bg-dark me-1">{{ $mat->materia->codigo_materia }}</span>
                                        <span class="fw-semibold text-dark">{{ $mat->materia->nombre_materia }}</span>
                                    @else
                                        <span class="text-muted">Sin materia</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success me-1">A: {{ $mat->asistencias_mat }}</span>
                                    <span class="badge bg-warning text-dark me-1">R: {{ $mat->retardos_mat }}</span>
                                    <span class="badge bg-info text-dark me-1">P: {{ $mat->permisos_mat }}</span>
                                    <span class="badge bg-danger">F: {{ $mat->faltas_mat }}</span>
                                </td>
                                <td>
                                    @if (is_null($pctM))
                                        <span class="text-muted small">Sin sesiones aún</span>
                                    @else
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-bold text-{{ $colorM === 'warning' ? 'dark' : $colorM }}">{{ $pctM }}%</span>
                                            <span class="text-muted">{{ $mat->total_sesiones }} sesiones</span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 7px;">
                                            <div class="progress-bar bg-{{ $colorM }}" role="progressbar" data-width="{{ $pctM }}"></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold {{ $mat->faltas_totales_mat >= 3 ? 'text-danger' : ($mat->faltas_totales_mat === 2 ? 'text-warning text-dark' : 'text-success') }}">
                                        {{ $mat->faltas_totales_mat }} / 3 faltas
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $mat->faltas_mat }} dir. + {{ $mat->faltas_retardos_mat }} por {{ $mat->retardos_mat }} ret.
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($mat->faltas_totales_mat >= 3)
                                        <span class="badge bg-danger">
                                            Sin Derecho a Examen (SD)
                                        </span>
                                    @elseif ($mat->faltas_totales_mat === 2)
                                        <span class="badge bg-warning text-dark">
                                            En Riesgo / Preventivo (2/3)
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            Con Derecho a Ordinario
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Aún no tienes materias aceptadas. Ve al inicio y haz clic en <strong>"Buscar y Solicitar Grupo"</strong> para inscribirte.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-width]').forEach(function (el) {
                el.style.width = el.getAttribute('data-width') + '%';
            });
        });
    </script>
</body>
</html>
