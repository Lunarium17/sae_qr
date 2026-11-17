<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Alumnos por CSV - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('partials.info-icon-css')
</head>
<body class="bg-light">

    @php
        $usuario = \Illuminate\Support\Facades\Auth::user();
        $avHue = abs(crc32($usuario->name ?? 'U')) % 360;
        $esPreview = isset($filas);
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">SAE QR | Importar Alumnos</a>
            <div class="d-flex align-items-center gap-2">
                @if ($usuario->foto_perfil)
                    <img src="{{ asset('storage/' . $usuario->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; border: 2px solid rgba(255,255,255,.5);">
                @else
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 34px; height: 34px; background: hsl({{ $avHue }}, 65%, 45%); font-size: .85rem;">{{ mb_strtoupper(mb_substr($usuario->name ?? 'U', 0, 1)) }}</span>
                @endif
                @php
                    $grupoOrigen = request('grupo_id') ? \App\Models\Grupo::find(request('grupo_id')) : null;
                @endphp
                @if ($grupoOrigen)
                    <a href="{{ route('grupos.show', $grupoOrigen->id) }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Grupo {{ $grupoOrigen->nombre_grupo }}
                    </a>
                    <a href="{{ route('profesor.dashboard') }}" class="btn btn-outline-secondary btn-sm text-light">
                        <i class="bi bi-house-door me-1"></i>Panel Principal
                    </a>
                @else
                    <a href="{{ url('/profesor/dashboard') }}" class="btn btn-light btn-sm text-dark fw-semibold rounded-3">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Panel
                    </a>
                @endif
                @include('partials.campana')
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-3">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <!-- Paso 2: Previsualizacion de la importacion -->
        @if ($esPreview)
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Paso 2 · Revisa antes de confirmar</h4>
                            <div class="text-muted small">
                                <i class="bi bi-book me-1"></i>{{ $materia->nombre_materia }}
                                @if ($materia->grupo)
                                    · Grupo {{ $materia->grupo->nombre_grupo }} · {{ $materia->grupo->carrera }} · {{ $materia->grupo->turno }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-success rounded-pill px-3 py-2">{{ count($filas) }} alumnos a inscribir</span>
                            @if (count($errores) > 0)
                                <span class="badge bg-danger rounded-pill px-3 py-2">{{ count($errores) }} filas con error</span>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Alumno</th>
                                    <th>Email</th>
                                    <th>Matrícula</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($filas as $fila)
                                    <tr>
                                        <td class="fw-semibold">{{ $fila['nombre'] }}</td>
                                        <td>{{ $fila['email'] }}</td>
                                        <td class="font-monospace">{{ $fila['matricula'] }}</td>
                                        <td class="text-center">
                                            @if ($fila['estado'] === 'nuevo')
                                                <span class="badge bg-primary">Cuenta nueva</span>
                                            @elseif ($fila['estado'] === 'cuenta')
                                                <span class="badge bg-info text-dark">Cuenta existente</span>
                                            @else
                                                <span class="badge bg-secondary">Ya estaba inscrito</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No hay alumnos válidos en el archivo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (count($errores) > 0)
                        <div class="alert alert-warning rounded-3 mb-3">
                            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Filas ignoradas ({{ count($errores) }}):</div>
                            <ul class="small mb-0">
                                @foreach (array_slice($errores, 0, 10) as $err)
                                    <li>Fila {{ $err['fila'] }} — {{ $err['nombre'] ?: '(sin nombre)' }} ({{ $err['email'] ?: 'sin email' }} · {{ $err['matricula'] ?: 'sin matrícula' }}): {{ $err['motivo'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('profesor.materias.importar.store') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold" {{ count($filas) === 0 ? 'disabled' : '' }}>
                                <i class="bi bi-check2-circle me-2"></i>Confirmar e inscribir {{ count($filas) }} alumnos
                            </button>
                        </form>
                        <a href="{{ route('profesor.materias.importar') }}" class="btn btn-outline-dark rounded-3 px-4 py-2">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Paso 1: Seleccionar destino y cargar CSV -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        Paso 1 · Elige dónde y sube el archivo
                        <span class="info-icon" tabindex="0" role="button" aria-label="Se creará una cuenta por alumno nuevo con su matrícula como contraseña inicial, y quedará inscrito directamente en la materia elegida.">
                            <i class="bi bi-info-circle"></i>
                            <span class="info-icon-text" role="tooltip">Se creará una cuenta por alumno nuevo con su matrícula como contraseña inicial, y quedará inscrito directamente en la materia elegida. Si el correo ya existe en el sistema, la cuenta se reutiliza y solo se inscribe en la materia.</span>
                        </span>
                    </h4>
                </div>

                <form action="{{ route('profesor.materias.importar.preview') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Carrera <span class="text-danger">*</span></label>
                        <select id="selCarrera" class="form-select rounded-3" required>
                            <option value="" selected disabled>Selecciona la carrera</option>
                            @foreach (array_keys($arbol) as $carrera)
                                <option value="{{ $carrera }}">{{ $carrera }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Grupo <span class="text-danger">*</span></label>
                        <select id="selGrupo" class="form-select rounded-3" required disabled>
                            <option value="" selected disabled>Selecciona el grupo</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Materia de destino <span class="text-danger">*</span></label>
                        <select id="selMateria" name="materia_id" class="form-select rounded-3" required disabled>
                            <option value="" selected disabled>Selecciona la materia</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            Archivo CSV de alumnos
                            <span class="info-icon" tabindex="0" role="button" aria-label="Columnas exactas: nombre, email, matricula.">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">El archivo debe tener las columnas exactas: nombre, email, matricula. La matrícula será la contraseña inicial del alumno nuevo.</span>
                            </span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="archivo_csv" class="form-control rounded-3" accept=".csv,text/csv" required>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <a href="{{ route('profesor.materias.plantilla') }}" class="btn btn-outline-primary btn-sm rounded-3">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i>Descargar plantilla
                            </a>
                            <code class="bg-light border rounded px-2 py-1 small">nombre, email, matricula</code>
                            <span class="info-icon" tabindex="0" role="button" aria-label="Máximo 200 alumnos por archivo.">
                                <i class="bi bi-info-circle"></i>
                                <span class="info-icon-text" role="tooltip">Puedes importar hasta 200 alumnos por archivo.</span>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-upload me-2"></i>Revisar archivo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const arbol = @json($arbol);
            const selCarrera = document.getElementById('selCarrera');
            const selGrupo = document.getElementById('selGrupo');
            const selMateria = document.getElementById('selMateria');
            if (!selCarrera || !selGrupo || !selMateria) return;

            function llenar(select, opciones, placeholder) {
                select.innerHTML = '';
                const vacia = document.createElement('option');
                vacia.value = '';
                vacia.textContent = placeholder;
                vacia.disabled = true;
                vacia.selected = true;
                select.appendChild(vacia);
                opciones.forEach(function (op) {
                    const opt = document.createElement('option');
                    opt.value = op.value;
                    opt.textContent = op.texto;
                    select.appendChild(opt);
                });
                select.disabled = opciones.length === 0;
            }

            selCarrera.addEventListener('change', function () {
                const carrera = arbol[this.value] || {};
                llenar(selGrupo, Object.keys(carrera).map(function (gid) {
                    return { value: gid, texto: carrera[gid].label + ' · ' + carrera[gid].periodo };
                }), 'Selecciona el grupo');
                llenar(selMateria, [], 'Selecciona la materia');
            });

            selGrupo.addEventListener('change', function () {
                const carrera = arbol[selCarrera.value] || {};
                const grupo = carrera[this.value] || { materias: [] };
                llenar(selMateria, grupo.materias.map(function (m) {
                    return { value: m.id, texto: m.texto };
                }), 'Selecciona la materia');
            });
        });
    </script>
</body>
</html>
