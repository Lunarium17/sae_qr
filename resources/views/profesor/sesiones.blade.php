<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sesiones de Clase y Escáner QR - SAE QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-camera-video me-2"></i>SAE QR | Control de Sesiones y Escáner</a>
            <a href="{{ url('/profesor/dashboard') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container my-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(!$sesionActiva)
            <!-- Formulario para Abrir Nueva Sesión de Clase -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h4 class="fw-bold text-dark mb-1">Abrir Nueva Sesión de Clase</h4>
                <p class="text-muted small mb-4">Selecciona la materia (junto con su grupo) y el tiempo de tolerancia para habilitar la lectura de códigos QR.</p>

                <form action="{{ route('sesiones.iniciar') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Materia y Grupo a Impartir</label>
                        <select name="materia_id" class="form-select rounded-3" required>
                            <option value="" disabled {{ empty($materiaSeleccionada) ? 'selected' : '' }}>Selecciona una asignatura...</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}" {{ (isset($materiaSeleccionada) && (int) $materiaSeleccionada === (int) $materia->id) ? 'selected' : '' }}>
                                    [{{ $materia->codigo_materia }}] {{ $materia->nombre_materia }}{{ $materia->grupo ? ' — Grupo: ' . $materia->grupo->nombre_grupo . ' (' . $materia->grupo->turno . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tolerancia (Minutos)</label>
                        <input type="number" name="tolerancia_minutos" class="form-control rounded-3" value="15" min="0" max="60" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bi bi-play-circle-fill me-2"></i>Iniciar Clase
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Panel de Sesión Activa y Cámara Escáner QR -->
            <div class="row g-4 mb-5">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                        <span class="badge bg-success mb-2 align-self-center px-3 py-2 rounded-pill">
                            <i class="bi bi-broadcast me-1"></i>SESIÓN ACTIVA EN CURSO
                        </span>
                        <h4 class="fw-bold text-dark mb-1">{{ $sesionActiva->materia->nombre_materia }}</h4>
                        @if($sesionActiva->materia->grupo)
                            <div class="mb-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3 py-1">
                                    Grupo: {{ $sesionActiva->materia->grupo->nombre_grupo }} ({{ $sesionActiva->materia->grupo->turno }})
                                </span>
                            </div>
                        @endif
                        <p class="text-muted small">Inicio: {{ $sesionActiva->hora_inicio }} | Tolerancia: {{ $sesionActiva->tolerancia_minutos }} min</p>

                        <!-- Visor de Cámara -->
                        <div id="reader" class="border rounded-4 overflow-hidden mx-auto my-3 bg-dark" style="width: 100%; max-width: 350px;"></div>

                        <div id="alerta-escaneo" class="alert d-none rounded-3 small py-2" role="alert"></div>

                        <div class="d-flex gap-2 justify-content-center mt-3">
                            <button id="btn-camara" onclick="iniciarCamara()" class="btn btn-primary rounded-3 px-3">
                                <i class="bi bi-camera-fill me-1"></i>Encender Cámara
                            </button>
                            <form action="{{ route('sesiones.finalizar', $sesionActiva->id) }}" method="POST" onsubmit="return confirm('¿Cerrar sesión de clase? Se asignará falta a los alumnos no escaneados.');">
                                @csrf
                                <button type="submit" class="btn btn-danger rounded-3 px-3">
                                    <i class="bi bi-stop-circle-fill me-1"></i>Cerrar Clase
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista de Asistencia en Tiempo Real -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Alumnos Registrados en esta Sesión</h5>
                            <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar Tabla
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Hora Escaneo</th>
                                        <th>Estado</th>
                                        <th class="text-center">Ajuste Manual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sesionActiva->asistencias as $asis)
                                        <tr>
                                            <td class="fw-semibold">{{ $asis->alumno->name }}</td>
                                            <td class="text-muted small">{{ $asis->timestamp_escaneo ?? 'Sin escaneo' }}</td>
                                            <td>
                                                @if($asis->estado_incidencia === 'asistencia')
                                                    <span class="badge bg-success">Asistencia</span>
                                                @elseif($asis->estado_incidencia === 'retardo')
                                                    <span class="badge bg-warning text-dark">Retardo</span>
                                                @elseif($asis->estado_incidencia === 'permiso')
                                                    <span class="badge bg-primary">Permiso</span>
                                                @else
                                                    <span class="badge bg-danger">Falta</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('asistencias.manual', $asis->id) }}" method="POST" class="d-inline-flex gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="estado_incidencia" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="asistencia" {{ $asis->estado_incidencia == 'asistencia' ? 'selected' : '' }}>Asistencia</option>
                                                        <option value="retardo" {{ $asis->estado_incidencia == 'retardo' ? 'selected' : '' }}>Retardo</option>
                                                        <option value="permiso" {{ $asis->estado_incidencia == 'permiso' ? 'selected' : '' }}>Permiso</option>
                                                        <option value="falta" {{ $asis->estado_incidencia == 'falta' ? 'selected' : '' }}>Falta</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Aún no se ha escaneado ningún código QR en esta sesión.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guardamos los datos de Blade en atributos HTML para que JS no marque error en VS Code -->
            <div id="datos-sesion" 
                 data-url="{{ route('qr.validar') }}" 
                 data-sesion-id="{{ $sesionActiva->id }}" 
                 class="d-none">
            </div>

            <!-- Script del Escáner HTML5-QRCode conectado al Backend -->
            <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
            <script>
                let html5QrCode;
                let escaneando = false;

                function iniciarCamara() {
                    if (escaneando) return;
                    html5QrCode = new Html5Qrcode("reader");
                    html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 220, height: 220 } },
                        onScanSuccess
                    );
                    escaneando = true;
                }

                function onScanSuccess(decodedText) {
                    html5QrCode.pause();

                    const datosEl = document.getElementById('datos-sesion');
                    const urlValidar = datosEl.getAttribute('data-url');
                    const sesionId = datosEl.getAttribute('data-sesion-id');

                    fetch(urlValidar, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            qr_token: decodedText,
                            sesion_clase_id: sesionId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        const alerta = document.getElementById('alerta-escaneo');
                        alerta.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
                        
                        if (data.status === 'success') {
                            alerta.classList.add('alert-success');
                        } else if (data.status === 'warning') {
                            alerta.classList.add('alert-warning');
                        } else {
                            alerta.classList.add('alert-danger');
                        }
                        alerta.innerText = data.message;

                        setTimeout(() => {
                            html5QrCode.resume();
                        }, 2500);
                    });
                }
            </script>
        @endif
    </div>

</body>
</html>
