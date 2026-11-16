<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Profesor - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .info-icon { position: relative; display: inline-flex; align-items: center; color: #0d6efd; cursor: help; vertical-align: middle; font-size: .8em; margin-left: .15rem; padding: .1rem; }
        .info-icon .info-icon-text { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); min-width: 240px; max-width: 320px; background: #212529; color: #fff; font-size: .75rem; font-weight: 400; line-height: 1.35; padding: .5rem .65rem; border-radius: .4rem; box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .2); opacity: 0; visibility: hidden; transition: opacity .15s; z-index: 10; text-align: left; }
        .info-icon:hover .info-icon-text, .info-icon:focus .info-icon-text, .info-icon:focus-within .info-icon-text { opacity: 1; visibility: visible; }
    </style>
</head>
<body class="bg-light">

    @php
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

        $totalSesiones = \App\Models\SesionClase::where('profesor_id', $profesorId)->count();
        $sesionesActivasCount = \App\Models\SesionClase::where('profesor_id', $profesorId)->where('estado', 'activa')->count();

        $alumnosAceptadosCount = $misMateriaIds->isNotEmpty()
            ? \App\Models\Matricula::whereIn('materia_id', $misMateriaIds)->where('estado', 'aceptado')->count()
            : 0;
        $solicitudesPendientes = $misMateriaIds->isNotEmpty()
            ? \App\Models\Matricula::whereIn('materia_id', $misMateriaIds)->where('estado', 'pendiente')->count()
            : 0;
        $justificantesPendientes = \App\Models\Justificante::where('estado_aprobacion', 'pendiente')
            ->whereHas('registroAsistencia.sesion', function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId);
            })
            ->count();

        $asistenciasCount = \App\Models\RegistroAsistencia::where('estado_incidencia', 'asistencia')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId))->count();
        $retardosCount    = \App\Models\RegistroAsistencia::where('estado_incidencia', 'retardo')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId))->count();
        $permisosCount    = \App\Models\RegistroAsistencia::where('estado_incidencia', 'permiso')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId))->count();
        $faltasCount      = \App\Models\RegistroAsistencia::where('estado_incidencia', 'falta')
            ->whereHas('sesion', fn($q) => $q->where('profesor_id', $profesorId))->count();

        $totalRegistros = $asistenciasCount + $retardosCount + $permisosCount + $faltasCount;
        $asistenciasEfectivas = $asistenciasCount + $retardosCount + $permisosCount;
        $porcentajeAsistencia = $totalRegistros > 0 ? round(($asistenciasEfectivas / $totalRegistros) * 100) : 0;

        $colorSemaforoGlobal = $porcentajeAsistencia >= 85 ? 'success' : ($porcentajeAsistencia >= 70 ? 'warning' : 'danger');

        // Datos para la gráfica comparativa de asistencia por Grupo (en las asignaturas que imparte este docente)
        $nombresGruposChart = [];
        $porcentajesGruposChart = [];
        foreach ($grupos as $g) {
            $mIds = $g->materias->pluck('id');
            if ($mIds->isEmpty()) continue;
            $nombresGruposChart[] = $g->nombre_grupo . ' (' . $g->turno . ')';
            $regsG = \App\Models\RegistroAsistencia::whereHas('sesion', function ($q) use ($mIds, $profesorId) {
                $q->where('profesor_id', $profesorId)->whereIn('materia_id', $mIds);
            })->get();
            $totG = $regsG->count();
            $efecG = $regsG->whereIn('estado_incidencia', ['asistencia', 'retardo', 'permiso'])->count();
            $porcentajesGruposChart[] = $totG > 0 ? round(($efecG / $totG) * 100) : 0;
        }

        // Detección de Alumnos en Riesgo de perder Derecho a Examen Ordinario (aplicando regla universitaria: 3 retardos = 1 falta)
        $matriculasAceptadas = $misMateriaIds->isNotEmpty()
            ? \App\Models\Matricula::with(['alumno', 'grupo', 'materia'])
                ->whereIn('materia_id', $misMateriaIds)
                ->where('estado', 'aceptado')
                ->get()
            : collect();

        $alumnosEnRiesgo = [];
        foreach ($matriculasAceptadas as $mat) {
            if (!$mat->materia || !$mat->alumno) continue;
            $regsAlumnoMat = \App\Models\RegistroAsistencia::where('user_id', $mat->user_id)
                ->whereHas('sesion', function ($q) use ($mat, $profesorId) {
                    $q->where('profesor_id', $profesorId)
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

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Panel de Profesor</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('profesor.perfil') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
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
        <!-- 1. Panel Principal de Bienvenida y Acciones Rápidas (Parte Superior) -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center mb-4">
            <h3 class="fw-bold text-dark mb-1">Bienvenido, {{ \Illuminate\Support\Facades\Auth::user()->name }}</h3>
            <p class="text-muted small mb-0">
                {{ \Illuminate\Support\Facades\Auth::user()->email }}
                | {{ \Illuminate\Support\Facades\Auth::user()->matricula_codigo ? 'Clave: ' . \Illuminate\Support\Facades\Auth::user()->matricula_codigo : 'Sin clave asignada' }}
            </p>

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
            </div>
        </div>

        <!-- 2. Tarjetas de KPIs Detallados en Tiempo Real -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 p-3 bg-white h-100">
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
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 border-start border-4 border-info shadow-sm rounded-4 p-3 bg-white h-100">
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
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 p-3 bg-white h-100">
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
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card border-0 border-start border-4 border-{{ $colorSemaforoGlobal }} shadow-sm rounded-4 p-3 bg-white h-100">
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
            </div>
        </div>

        <!-- 3. Visualización Analítica: Gráficas Interactivas (Chart.js) -->
        <div class="row g-4 mb-4">
            <!-- Gráfica de Dona: Desglose Global de Incidencias -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Distribución de Incidencias</h5>
                        <span class="badge bg-dark rounded-pill">{{ $totalRegistros }} pases</span>
                    </div>
                    @if ($totalRegistros > 0)
                        <div class="mx-auto" style="max-width: 260px;">
                            <canvas id="chartIncidenciasGlobal"></canvas>
                        </div>
                        <div class="row text-center mt-3 g-2">
                            <div class="col-3">
                                <span class="badge bg-success w-100 py-2">Asist: {{ $asistenciasCount }}</span>
                            </div>
                            <div class="col-3">
                                <span class="badge bg-warning text-dark w-100 py-2">Ret: {{ $retardosCount }}</span>
                            </div>
                            <div class="col-3">
                                <span class="badge bg-info text-dark w-100 py-2">Perm: {{ $permisosCount }}</span>
                            </div>
                            <div class="col-3">
                                <span class="badge bg-danger w-100 py-2">Faltas: {{ $faltasCount }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted my-auto py-5">
                            <i class="bi bi-bar-chart display-4 d-block mb-2 opacity-50"></i>
                            Aún no hay registros de asistencia para graficar.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gráfica de Barras: Comparativa de Asistencia por Grupo -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-0">% de Asistencia por Grupo</h5>
                    </div>
                    @if ($gruposCount > 0)
                        <div style="height: 280px;">
                            <canvas id="chartGruposComparativa"></canvas>
                        </div>
                    @else
                        <div class="text-center text-muted my-auto py-5">
                            <i class="bi bi-folder-x display-4 d-block mb-2 opacity-50"></i>
                            Crea tu primer grupo académico para visualizar la comparativa.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. Seguimiento de Derecho a Examen Ordinario y Riesgo por Faltas (Regla: 3 Retardos = 1 Falta) -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        Control de Derecho a Examen Ordinario
                        <span class="info-icon" tabindex="0" role="button" aria-label="3 retardos = 1 falta. Con 2 faltas: en riesgo. Con 3: sin derecho a examen ordinario.">
                            <i class="bi bi-info-circle"></i>
                            <span class="info-icon-text" role="tooltip">3 retardos = 1 falta. Con 2 faltas: en riesgo. Con 3: sin derecho a examen ordinario.</span>
                        </span>
                    </h5>
                </div>
                <span class="badge {{ count($alumnosEnRiesgo) > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-3 py-2">
                    {{ count($alumnosEnRiesgo) }} Casos Detectados
                </span>
            </div>

            <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Grupo / Semestre</th>
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
                                    <div class="text-muted small">{{ $caso['alumno']->email }}</div>
                                </td>
                                <td>
                                    @if ($caso['grupo'])
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            {{ $caso['grupo']->nombre_grupo }} ({{ $caso['grupo']->grado }})
                                        </span>
                                        @if ($caso['grupo']->carrera)
                                            <div class="small text-muted mt-1">{{ $caso['grupo']->carrera }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $caso['materia']->nombre_materia }}</td>
                                <td class="text-center">
                                    <div class="fw-bold {{ $caso['critico'] ? 'text-danger' : 'text-warning text-dark' }}">
                                        {{ $caso['faltas'] }} / 3 faltas
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $caso['faltas_directas'] }} dir. + {{ $caso['faltas_retardos'] }} por {{ $caso['retardos'] }} ret.
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($caso['critico'])
                                        <span class="badge bg-danger">Sin Derecho a Examen (SD)</span>
                                    @else
                                        <span class="badge bg-warning text-dark">En Riesgo / Preventivo (2/3)</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('materias.show', $caso['materia']->id) }}" class="btn btn-sm btn-outline-dark rounded-3">
                                        <i class="bi bi-eye me-1"></i>Ver en Materia
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Ningún alumno supera las 2 faltas sin justificar en este momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Módulos del Sprint 6: Reportes y Justificantes -->
        <div class="row my-4 g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark">Exportación y Reportes</h5>

                    <a href="{{ route('profesor.reportes') }}" class="btn btn-outline-primary btn-sm rounded-3 mt-auto">
                        <i class="bi bi-bar-chart-line me-1"></i>Ver y Exportar Reportes (PDF / Excel)
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="fw-bold text-dark">Permisos y Justificantes</h5>
                        @if ($justificantesPendientes > 0)
                            <span class="badge bg-warning text-dark rounded-pill">{{ $justificantesPendientes }} pendientes</span>
                        @endif
                    </div>
                    <a href="{{ route('profesor.justificantes') }}" class="btn btn-outline-info btn-sm rounded-3 mt-auto">
                        <i class="bi bi-file-earmark-text me-1"></i>Revisar Justificantes Pendientes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="datos-charts-dashboard"
         data-asistencias="{{ $asistenciasCount }}"
         data-retardos="{{ $retardosCount }}"
         data-permisos="{{ $permisosCount }}"
         data-faltas="{{ $faltasCount }}"
         data-grupos-labels="{{ json_encode($nombresGruposChart) }}"
         data-grupos-valores="{{ json_encode($porcentajesGruposChart) }}"
         class="d-none"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-width]').forEach(function (el) {
                el.style.width = el.getAttribute('data-width') + '%';
            });
            const datosEl = document.getElementById('datos-charts-dashboard');
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
                const colores = valores.map(v => v >= 85 ? '#198754' : (v >= 70 ? '#ffc107' : '#dc3545'));

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
