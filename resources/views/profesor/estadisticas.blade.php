<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas y Derecho a Examen - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        @include('partials.panel-css')
</head>
<body>

    @php
        $profesor = \Illuminate\Support\Facades\Auth::user();
        $profesorId = \Illuminate\Support\Facades\Auth::id();

        // Filtros de vista: general (todo) | materia (una asignatura) | carrera (comparativa)
        $vista = request('vista', 'general');
        $carreraFiltro = $vista === 'carrera' ? request('carrera') : null;

        $misMaterias = \App\Models\Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->get();
        $misMateriaIds = $misMaterias->pluck('id');
        $materiasCount = $misMaterias->count();

        // Materia seleccionada cuando la vista es por materia
        $materiaSel = null;
        if ($vista === 'materia') {
            $mid = (int) request('materia_id');
            $materiaSel = $misMaterias->firstWhere('id', $mid) ?? $misMaterias->first();
        }

        $grupos = \App\Models\Grupo::with(['materias' => function ($q) use ($profesorId) {
            $q->where('profesor_id', $profesorId)->orWhereNull('profesor_id');
        }])->get();
        $gruposCount = $grupos->count();

        // Carreras en las que imparte este docente (derivadas de sus grupos)
        $carrerasDocente = $grupos->pluck('carrera')->filter()->unique()->values();

        // Alcance de las incidencias según el filtro activo (solo sesiones del semestre vigente)
        $countIncidencias = function ($estado) use ($profesorId, $materiaSel) {
            return \App\Models\RegistroAsistencia::where('estado_incidencia', $estado)
                ->whereHas('sesion', function ($q) use ($profesorId, $materiaSel) {
                    $q->where('profesor_id', $profesorId)
                      ->where('archivado', false);
                    if ($materiaSel) {
                        $q->where('materia_id', $materiaSel->id);
                    }
                })->count();
        };
        $asistenciasCount = $countIncidencias('asistencia');
        $retardosCount    = $countIncidencias('retardo');
        $permisosCount    = $countIncidencias('permiso');
        $faltasCount      = $countIncidencias('falta');

        $totalRegistros = $asistenciasCount + $retardosCount + $permisosCount + $faltasCount;

        // Barras según la vista: por Grupo (general) | por Materia | por Carrera
        $nombresGruposChart = [];
        $porcentajesGruposChart = [];
        foreach ($grupos as $g) {
            $mIds = $g->materias->pluck('id');
            if ($mIds->isEmpty()) continue;
            $nombresGruposChart[] = $g->nombre_grupo . ' (' . $g->turno . ')';
            $regsG = \App\Models\RegistroAsistencia::whereHas('sesion', function ($q) use ($mIds, $profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->where('archivado', false)
                  ->whereIn('materia_id', $mIds);
            })->get();
            $totG = $regsG->count();
            $efecG = $regsG->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
            $porcentajesGruposChart[] = $totG > 0 ? round(($efecG / $totG) * 100) : 0;
        }

        $nombresMateriasChart = [];
        $porcentajesMateriasChart = [];
        foreach ($misMaterias as $m) {
            $regsM = \App\Models\RegistroAsistencia::whereHas('sesion', function ($q) use ($m, $profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->where('archivado', false)
                  ->where('materia_id', $m->id);
            })->get();
            $totM = $regsM->count();
            $efecM = $regsM->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
            $nombresMateriasChart[] = ($m->codigo_materia ? $m->codigo_materia . ' · ' : '') . $m->nombre_materia;
            $porcentajesMateriasChart[] = $totM > 0 ? round(($efecM / $totM) * 100) : 0;
        }

        $nombresCarrerasChart = [];
        $porcentajesCarrerasChart = [];
        $coloresCarrerasChart = [];
        foreach ($carrerasDocente as $carrera) {
            $mIdsCarrera = $grupos->filter(fn($g) => ($g->carrera ?? '') === $carrera)
                ->flatMap(fn($g) => $g->materias->pluck('id'))
                ->unique();
            if ($mIdsCarrera->isEmpty()) continue;
            $regsC = \App\Models\RegistroAsistencia::whereHas('sesion', function ($q) use ($mIdsCarrera, $profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->where('archivado', false)
                  ->whereIn('materia_id', $mIdsCarrera);
            })->get();
            $totC = $regsC->count();
            $efecC = $regsC->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
            $nombresCarrerasChart[] = $carrera;
            $porcentajesCarrerasChart[] = $totC > 0 ? round(($efecC / $totC) * 100) : 0;
            $coloresCarrerasChart[] = \App\Support\CarreraColor::color($carrera);
        }

        if ($vista === 'materia') {
            $barLabels = $nombresMateriasChart;
            $barValores = $porcentajesMateriasChart;
            $barColores = null;
            $barTitulo = '% de Asistencia por Materia';
        } elseif ($vista === 'carrera') {
            $barLabels = $nombresCarrerasChart;
            $barValores = $porcentajesCarrerasChart;
            $barColores = $coloresCarrerasChart;
            $barTitulo = '% de Asistencia por Carrera';
        } else {
            $barLabels = $nombresGruposChart;
            $barValores = $porcentajesGruposChart;
            $barColores = null;
            $barTitulo = '% de Asistencia por Grupo';
        }

        // Detección de Alumnos en Riesgo de perder Derecho a Examen Ordinario (aplicando regla universitaria: 3 retardos = 1 falta)
        $matriculasAceptadas = $misMateriaIds->isNotEmpty()
            ? \App\Models\Matricula::with(['alumno', 'grupo', 'materia'])
                ->whereIn('materia_id', $misMateriaIds)
                ->where('estado', 'aceptado')
                ->get()
            : collect();

        // Aplicar el filtro activo a la tabla de riesgo
        $matriculasFiltradas = $matriculasAceptadas;
        if ($vista === 'materia' && $materiaSel) {
            $matriculasFiltradas = $matriculasFiltradas->filter(fn($m) => (int) $m->materia_id === (int) $materiaSel->id);
        }
        if ($carreraFiltro) {
            $matriculasFiltradas = $matriculasFiltradas->filter(fn($m) => ($m->grupo->carrera ?? '') === $carreraFiltro);
        }

        $alumnosEnRiesgo = [];
        foreach ($matriculasFiltradas as $mat) {
            if (!$mat->materia || !$mat->alumno) continue;
            $regsAlumnoMat = \App\Models\RegistroAsistencia::where('user_id', $mat->user_id)
                ->whereHas('sesion', function ($q) use ($mat, $profesorId) {
                    $q->where('profesor_id', $profesorId)
                      ->where('archivado', false)
                      ->where('materia_id', $mat->materia_id);
                })
                ->get();

            $faltasDirectas   = $regsAlumnoMat->where('estado_incidencia', 'falta')->count();
            $retardosAcum     = $regsAlumnoMat->where('estado_incidencia', 'retardo')->count();
            $faltasPorRetardo = intdiv($retardosAcum, 3);
            $faltasTotales    = $faltasDirectas + $faltasPorRetardo;

            if ($faltasTotales >= 2) {
                $alumnosEnRiesgo[] = [
                    'alumno'           => $mat->alumno,
                    'grupo'            => $mat->grupo,
                    'materia'          => $mat->materia,
                    'faltas'           => $faltasTotales,
                    'faltas_directas'  => $faltasDirectas,
                    'retardos'         => $retardosAcum,
                    'faltas_retardos'  => $faltasPorRetardo,
                    'critico'          => $faltasTotales >= 3,
                ];
            }
        }
        usort($alumnosEnRiesgo, fn($a, $b) => $b['faltas'] <=> $a['faltas']);
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-editorial shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="#">SAE QR | Estadísticas</a>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('profesor.dashboard') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
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

        <!-- Filtros de vista: General / Por Materia / Por Carrera -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <a class="btn btn-sm rounded-pill px-3 py-2 {{ $vista === 'general' ? 'btn-institucional' : 'btn-outline-secondary' }}" href="{{ url()->current() }}?vista=general">
                <i class="bi bi-grid-1x2 me-1"></i>General
            </a>
            <a class="btn btn-sm rounded-pill px-3 py-2 {{ $vista === 'materia' ? 'btn-institucional' : 'btn-outline-secondary' }}" href="{{ url()->current() }}?vista=materia">
                <i class="bi bi-book me-1"></i>Por Materia
            </a>
            <a class="btn btn-sm rounded-pill px-3 py-2 {{ $vista === 'carrera' ? 'btn-institucional' : 'btn-outline-secondary' }}" href="{{ url()->current() }}?vista=carrera">
                <i class="bi bi-mortarboard me-1"></i>Por Carrera
            </a>
        </div>

        <!-- Selector de materia cuando la vista es por materia -->
        @if ($vista === 'materia')
            <div class="card rounded-4 p-3 bg-white mb-4">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-0 small">Materia a analizar</label>
                    </div>
                    <div class="col-md-8">
                        <select class="form-select rounded-3" onchange="window.location='{{ url()->current() }}?vista=materia&materia_id=' + this.value;">
                            @foreach ($misMaterias as $m)
                                <option value="{{ $m->id }}" {{ $materiaSel && (int) $materiaSel->id === (int) $m->id ? 'selected' : '' }}>
                                    [{{ $m->codigo_materia }}] {{ $m->nombre_materia }}{{ $m->grupo ? ' · ' . $m->grupo->nombre_grupo : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endif

        <!-- 1. Gráficas Interactivas (Chart.js) -->
        <div class="row g-4 mb-4">
            <!-- Gráfica de Dona: Desglose de Incidencias según el filtro -->
            <div class="col-lg-5">
                <div class="card rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Distribución de Incidencias</h5>
                        <span class="badge badge-estado badge-permiso rounded-pill">{{ $totalRegistros }} pases</span>
                    </div>
                    @if ($materiaSel)
                        <div class="small text-primary fw-semibold mb-2"><i class="bi bi-funnel me-1"></i>{{ $materiaSel->nombre_materia }}</div>
                    @endif
                    @if ($totalRegistros > 0)
                        <div class="mx-auto" style="max-width: 260px;">
                            <canvas id="chartIncidenciasGlobal"></canvas>
                        </div>
                    @else
                        <div class="text-center texto-slate my-auto py-5">
                            <i class="bi bi-bar-chart display-4 d-block mb-2 opacity-50"></i>
                            Aún no hay registros de asistencia para graficar.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gráfica de Barras: Comparativa según la vista -->
            <div class="col-lg-7">
                <div class="card rounded-4 p-4 bg-white h-100">
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-0">{{ $barTitulo }}</h5>
                    </div>
                    @if (count($barLabels) > 0)
                        <div style="height: 280px;">
                            <canvas id="chartGruposComparativa"></canvas>
                        </div>
                    @else
                        <div class="text-center texto-slate my-auto py-5">
                            <i class="bi bi-folder-x display-4 d-block mb-2 opacity-50"></i>
                            Sin datos suficientes para esta comparativa.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Control de Derecho a Examen Ordinario (Regla: 3 Retardos = 1 Falta) -->
        <div class="card rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Control de Derecho a Examen Ordinario</h5>
                    <div class="texto-slate small">3 retardos = 1 falta · máx. 3 faltas.</div>
                    @if ($materiaSel)
                        <div class="small text-primary fw-semibold"><i class="bi bi-funnel me-1"></i>Filtro: {{ $materiaSel->nombre_materia }}</div>
                    @elseif ($carreraFiltro)
                        <div class="small fw-semibold" style="color: {{ \App\Support\CarreraColor::color($carreraFiltro) }}"><i class="bi bi-funnel me-1"></i>Filtro: {{ $carreraFiltro }}</div>
                    @endif
                </div>
                <span class="badge {{ count($alumnosEnRiesgo) > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-2">
                    {{ count($alumnosEnRiesgo) }} Casos Detectados
                </span>
            </div>

            <!-- Chips de filtro por carrera (vista Por Carrera) -->
            @if ($vista === 'carrera')
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a class="badge rounded-pill px-3 py-2 text-decoration-none {{ !$carreraFiltro ? 'badge-estado badge-permiso' : 'badge-estado neutra-slate' }}" href="{{ url()->current() }}?vista=carrera">Todas las carreras</a>
                    @foreach ($carrerasDocente as $c)
                        <a class="badge rounded-pill px-3 py-2 text-decoration-none" style="{{ $carreraFiltro === $c ? \App\Support\CarreraColor::badge($c) : \App\Support\CarreraColor::suave($c) }}" href="{{ url()->current() }}?vista=carrera&carrera={{ urlencode($c) }}">
                            {{ $c }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-moderna table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Grupo / Carrera</th>
                            <th>Materia</th>
                            <th class="text-center">Faltas Computadas</th>
                            <th class="text-center">Derecho a Examen Ordinario</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alumnosEnRiesgo as $caso)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $caso['alumno']->name }}</div>
                                    <div class="texto-slate small">{{ $caso['alumno']->email }}</div>
                                </td>
                                <td>
                                    @if ($caso['grupo'])
                                        <span class="badge rounded-pill" style="{{ \App\Support\CarreraColor::suave($caso['grupo']->carrera ?? '') }}">
                                            {{ $caso['grupo']->nombre_grupo }} ({{ $caso['grupo']->grado }})
                                        </span>
                                        @if ($caso['grupo']->carrera)
                                            <div class="small mt-1 d-flex align-items-start gap-1">
                                                <span class="d-inline-block rounded-circle flex-shrink-0 mt-1" style="width: 8px; height: 8px; background: {{ \App\Support\CarreraColor::color($caso['grupo']->carrera) }};"></span>
                                                <span class="fw-semibold" style="color: {{ \App\Support\CarreraColor::color($caso['grupo']->carrera) }}; font-size: .75rem; line-height: 1.25;">{{ $caso['grupo']->carrera }}</span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="texto-slate">-</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $caso['materia']->nombre_materia }}</td>
                                <td class="text-center">
                                    <div class="fw-bold {{ $caso['critico'] ? 'text-danger' : 'text-warning text-dark' }}">
                                        {{ $caso['faltas'] }} / 3 faltas
                                    </div>
                                    <div class="texto-slate" style="font-size: 0.75rem;">
                                        {{ $caso['faltas_directas'] }} dir. + {{ $caso['faltas_retardos'] }} por {{ $caso['retardos'] }} ret.
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($caso['critico'])
                                        <span class="badge badge-estado badge-falta">Sin Derecho a Examen (SD)</span>
                                    @else
                                        <span class="badge badge-estado badge-retardo">En Riesgo / Preventivo (2/3)</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('materias.show', $caso['materia']->id) }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                        <i class="bi bi-eye me-1"></i>Ver en Materia
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 texto-slate">
                                    Ningún alumno supera las 2 faltas sin justificar en este filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="datos-charts-estadisticas"
         data-asistencias="{{ $asistenciasCount }}"
         data-retardos="{{ $retardosCount }}"
         data-permisos="{{ $permisosCount }}"
         data-faltas="{{ $faltasCount }}"
         data-grupos-labels="{{ json_encode($barLabels) }}"
         data-grupos-valores="{{ json_encode($barValores) }}"
         data-colores-custom="{{ json_encode($barColores) }}"
         class="d-none"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const datosEl = document.getElementById('datos-charts-estadisticas');
            const canvasDona = document.getElementById('chartIncidenciasGlobal');
            if (canvasDona && datosEl) {
                new Chart(canvasDona, {
                    type: 'doughnut',
                    data: {
                        labels: ['Asistencias', 'Retardos', 'Permisos', 'Faltas'],
                        datasets: [{
                            data: [
                                Number(datosEl.dataset.asistencias),
                                Number(datosEl.dataset.retardos),
                                Number(datosEl.dataset.permisos),
                                Number(datosEl.dataset.faltas)
                            ],
                            backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        cutout: '62%'
                    }
                });
            }

            const canvasBarras = document.getElementById('chartGruposComparativa');
            if (canvasBarras && datosEl) {
                const labels = JSON.parse(datosEl.dataset.gruposLabels || '[]');
                const valores = JSON.parse(datosEl.dataset.gruposValores || '[]');
                const coloresCustom = JSON.parse(datosEl.dataset.coloresCustom || 'null');
                const colores = coloresCustom || valores.map(v => v >= 85 ? '#198754' : (v >= 70 ? '#2563EB' : '#dc3545'));

                new Chart(canvasBarras, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '% de Asistencia Efectiva',
                            data: valores,
                            backgroundColor: colores,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: value => value + '%' }
                            },
                            x: {
                                ticks: { maxRotation: 45, minRotation: 0 }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
