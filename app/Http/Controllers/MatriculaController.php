<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MatriculaController extends Controller
{
    // ==========================================
    // FUNCIONES PARA EL ALUMNO (Matrículas Flexibles e Independientes por Asignatura)
    // ==========================================

    // Mostrar vista para seleccionar grupo y buscar asignaturas dinámicamente (sin cargar todas las materias de golpe)
    public function crearSolicitud()
    {
        $grupos = Grupo::orderBy('nombre_grupo')->get();
        $misMatriculas = Matricula::with(['grupo', 'materia.profesor'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('alumno.solicitar_grupo', compact('grupos', 'misMatriculas'));
    }

    // Endpoint AJAX para obtener dinámicamente las asignaturas de un grupo (con filtro opcional por texto)
    public function materiasPorGrupo(Request $request, Grupo $grupo)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Materia::with('profesor')
            ->where('grupo_id', $grupo->id);

        if ($q !== '') {
            $qEscapado = addcslashes($q, '%_\\');
            $query->where(function ($sub) use ($qEscapado) {
                $sub->where('nombre_materia', 'like', "%{$qEscapado}%")
                    ->orWhere('codigo_materia', 'like', "%{$qEscapado}%");
            });
        }

        $materias = $query->orderBy('nombre_materia')->limit(50)->get();

        $matriculasAlumno = Matricula::where('user_id', Auth::id())
            ->where('grupo_id', $grupo->id)
            ->pluck('estado', 'materia_id');

        $resultado = $materias->map(function ($m) use ($matriculasAlumno) {
            return [
                'id'               => $m->id,
                'codigo_materia'   => $m->codigo_materia,
                'nombre_materia'   => $m->nombre_materia,
                'profesor_nombre'  => optional($m->profesor)->name ?? 'Docente por asignar',
                'estado_matricula' => $matriculasAlumno->get($m->id), // null, 'pendiente', 'aceptado', 'rechazado'
            ];
        });

        return response()->json($resultado);
    }

    // Guardar solicitud individual para la combinación exacta (MateriaID + GrupoID)
    public function guardarSolicitud(Request $request)
    {
        $request->validate([
            'grupo_id'   => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
        ]);

        // Verificar que la asignatura pertenezca al grupo seleccionado
        $materia = Materia::findOrFail($request->materia_id);
        if (!is_null($materia->grupo_id) && (int) $materia->grupo_id !== (int) $request->grupo_id) {
            return back()->with('error', 'La asignatura seleccionada no corresponde al grupo elegido.');
        }

        // Verificar que el alumno no haya solicitado ya la misma combinación (GrupoID + MateriaID)
        $existe = Matricula::where('user_id', Auth::id())
            ->where('grupo_id', $request->grupo_id)
            ->where('materia_id', $request->materia_id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya enviaste una solicitud para esta asignatura en el grupo seleccionado.');
        }

        Matricula::create([
            'user_id'    => Auth::id(),
            'grupo_id'   => $request->grupo_id,
            'materia_id' => $request->materia_id,
            'estado'     => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud de inscripción por asignatura enviada. El docente titular de esa materia revisará tu solicitud.');
    }

    // ==========================================
    // FUNCIONES PARA EL PROFESOR (Revisión y Búsqueda Dinámica por Asignatura y Grupo)
    // ==========================================

    // Endpoint AJAX: Buscar alumnos por nombre, matrícula o correo (máximo 15 resultados)
    public function buscarAlumnos(Request $request)
    {
        $termino   = trim((string) $request->query('q', ''));
        $materiaId = (int) $request->query('materia_id', 0);

        if (mb_strlen($termino) < 2) {
            return response()->json([]);
        }

        $terminoEscapado = addcslashes($termino, '%_\\');
        $alumnos = User::where('rol', 'alumno')
            ->where(function ($q) use ($terminoEscapado) {
                $q->where('name', 'like', "%{$terminoEscapado}%")
                  ->orWhere('matricula_codigo', 'like', "%{$terminoEscapado}%")
                  ->orWhere('email', 'like', "%{$terminoEscapado}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email', 'matricula_codigo']);

        $idsAceptados = [];
        if ($materiaId > 0) {
            $idsAceptados = Matricula::where('materia_id', $materiaId)
                ->where('estado', 'aceptado')
                ->whereIn('user_id', $alumnos->pluck('id'))
                ->pluck('user_id')
                ->toArray();
        }

        $resultado = $alumnos->map(function ($al) use ($idsAceptados) {
            return [
                'id'               => $al->id,
                'name'             => $al->name,
                'email'            => $al->email,
                'matricula_codigo' => $al->matricula_codigo ?? 'Sin matrícula',
                'ya_inscrito'      => in_array($al->id, $idsAceptados),
            ];
        });

        return response()->json($resultado);
    }

    // Listar solicitudes e inscripciones de las asignaturas del profesor con buscador, filtros y paginación
    public function indexSolicitudes(Request $request)
    {
        $profesorId    = Auth::id();
        $buscar        = trim((string) $request->query('buscar', ''));
        $estadoFiltro  = $request->query('estado', 'todos');
        $materiaFiltro = (int) $request->query('materia_id', 0);

        $misMaterias = Materia::with('grupo')
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->orderBy('grupo_id')
            ->get();

        $query = Matricula::with(['alumno', 'grupo', 'materia.profesor'])
            ->whereHas('materia', function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            });

        if (in_array($estadoFiltro, ['pendiente', 'aceptado', 'rechazado'], true)) {
            $query->where('estado', $estadoFiltro);
        }

        if ($materiaFiltro > 0) {
            $query->where('materia_id', $materiaFiltro);
        }

        if ($buscar !== '') {
            $buscarEscapado = addcslashes($buscar, '%_\\');
            $query->whereHas('alumno', function ($q) use ($buscarEscapado) {
                $q->where('name', 'like', "%{$buscarEscapado}%")
                  ->orWhere('email', 'like', "%{$buscarEscapado}%")
                  ->orWhere('matricula_codigo', 'like', "%{$buscarEscapado}%");
            });
        }

        $solicitudes = $query
            ->orderByRaw("FIELD(estado, 'pendiente', 'aceptado', 'rechazado')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('profesor.solicitudes', compact(
            'solicitudes',
            'misMaterias',
            'buscar',
            'estadoFiltro',
            'materiaFiltro'
        ));
    }

    // Inscribir manualmente a un alumno en una combinación exacta (GrupoID + MateriaID) que imparte el profesor
    public function inscribirManual(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'grupo_id'   => 'nullable|exists:grupos,id',
        ], [
            'user_id.required'    => 'Debes buscar y seleccionar un alumno de la lista.',
            'materia_id.required' => 'Debes seleccionar una asignatura.',
        ]);

        $profesorId = Auth::id();
        $alumno  = User::where('id', $request->user_id)->where('rol', 'alumno')->firstOrFail();
        $materia = Materia::findOrFail($request->materia_id);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) $profesorId) {
            return back()->with('error', 'Solo puedes inscribir alumnos en las asignaturas que te corresponden impartir.');
        }

        $grupoId = $materia->grupo_id ?? $request->grupo_id;

        if (!$grupoId) {
            return back()->with('error', 'La asignatura debe estar asociada a un grupo para inscribir alumnos.');
        }

        $matricula = Matricula::updateOrCreate(
            [
                'user_id'    => $alumno->id,
                'grupo_id'   => $grupoId,
                'materia_id' => $materia->id,
            ],
            [
                'estado' => 'aceptado',
            ]
        );

        $accion = $matricula->wasRecentlyCreated ? 'inscrito' : 'actualizado como aceptado';

        return back()->with('success', 'El alumno ' . $alumno->name . ' ha sido ' . $accion . ' en ' . $materia->nombre_materia . '.');
    }

    // Aprobar o rechazar solicitud únicamente de asignaturas que imparte este profesor
    public function actualizarEstado(Request $request, Matricula $matricula)
    {
        $request->validate([
            'estado' => 'required|in:aceptado,rechazado,pendiente',
        ]);

        $matricula->loadMissing('materia');
        if ($matricula->materia && !is_null($matricula->materia->profesor_id) && (int) $matricula->materia->profesor_id !== (int) Auth::id()) {
            return back()->with('error', 'No tienes permiso para gestionar solicitudes de una asignatura impartida por otro docente.');
        }

        $matricula->update([
            'estado' => $request->estado,
        ]);

        $mensaje = match ($request->estado) {
            'aceptado'  => 'Solicitud aprobada: el alumno ha quedado inscrito en tu asignatura y grupo.',
            'rechazado' => 'Solicitud rechazada para esta asignatura.',
            default     => 'Estado de la inscripción actualizado.',
        };

        return back()->with('success', $mensaje);
    }

    // Dar de baja (eliminar inscripción) a un alumno de una asignatura que imparte este profesor
    public function destroy(Matricula $matricula)
    {
        $matricula->loadMissing(['alumno', 'materia']);
        if ($matricula->materia && !is_null($matricula->materia->profesor_id) && (int) $matricula->materia->profesor_id !== (int) Auth::id()) {
            return back()->with('error', 'No tienes permiso para dar de baja alumnos de una asignatura impartida por otro docente.');
        }

        $nombreAlumno = $matricula->alumno->name ?? 'El alumno';
        $matricula->delete();

        return back()->with('success', $nombreAlumno . ' fue dado de baja de la asignatura correctamente.');
    }

    // ==========================================
    // IMPORTACION DE ALUMNOS POR CSV (docente)
    // ==========================================

    // Paso 1: formulario con Carrera -> Grupo -> Materia y plantilla descargable
    public function importarForm()
    {
        return view('materias.importar', ['arbol' => $this->arbolDocente()]);
    }

    // Estructura Carrera -> Grupo -> Materias (solo materias del docente) para los menus encadenados
    private function arbolDocente()
    {
        $profesorId = Auth::id();

        $grupos = Grupo::with(['materias' => function ($q) use ($profesorId) {
            $q->where(function ($sub) use ($profesorId) {
                $sub->where('profesor_id', $profesorId)
                    ->orWhereNull('profesor_id');
            })->orderBy('nombre_materia');
        }])->orderBy('carrera')->orderBy('grado')->get();

        $arbol = [];
        foreach ($grupos as $g) {
            $carrera = $g->carrera ?: 'Carrera general';
            if ($g->materias->isEmpty()) {
                continue;
            }
            $arbol[$carrera][$g->id] = [
                'label'    => $g->nombre_grupo . ' · ' . $g->grado . ' · ' . $g->turno,
                'periodo'  => $g->periodo_lectivo,
                'materias' => $g->materias->map(fn ($m) => [
                    'id'    => $m->id,
                    'texto' => ($m->codigo_materia ? '[' . $m->codigo_materia . '] ' : '') . $m->nombre_materia,
                ])->values(),
            ];
        }

        return $arbol;
    }

    // Plantilla CSV descargable con el formato exacto
    public function plantillaCsv()
    {
        $lineas = [
            ['nombre', 'email', 'matricula'],
            ['Ana Ejemplo Import', 'ana.ejemplo@correo.com', 'IMP-0001'],
            ['Beto Ejemplo Import', 'beto.ejemplo@correo.com', 'IMP-0002'],
        ];

        $csv = '';
        foreach ($lineas as $linea) {
            $csv .= implode(',', $linea) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_alumnos_saeqr.csv"',
        ]);
    }

    // Paso 2: subir el CSV, validarlo y mostrar la previsualizacion antes de confirmar
    public function importarPreview(Request $request)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'archivo_csv' => 'required|file|max:2048|mimetypes:text/plain,text/csv,application/csv,text/comma-separated-values',
        ]);

        $profesorId = Auth::id();
        $materia = Materia::with('grupo')->findOrFail($request->materia_id);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) $profesorId) {
            return back()->with('error', 'Solo puedes importar alumnos a las asignaturas que tú impartes.');
        }

        $contenido = (string) file_get_contents($request->file('archivo_csv')->getRealPath());
        $contenido = str_replace("\xEF\xBB\xBF", '', $contenido); // Quitar BOM de Excel
        $lineas = preg_split('/\r\n|\r|\n/', trim($contenido)) ?: [];

        if (count($lineas) < 2) {
            return back()->with('error', 'El archivo no contiene filas de alumnos. Descarga la plantilla para el formato correcto.');
        }

        $encabezado = array_map(fn ($c) => strtolower(trim($c)), str_getcsv(array_shift($lineas)));
        $idxNombre    = array_search('nombre', $encabezado, true);
        $idxEmail     = array_search('email', $encabezado, true);
        $idxMatricula = array_search('matricula', $encabezado, true);

        if ($idxNombre === false || $idxEmail === false || $idxMatricula === false) {
            return back()->with('error', 'El CSV debe tener exactamente las columnas: nombre, email, matricula (usa la plantilla descargable).');
        }

        $filas = [];
        $errores = [];
        $vistos = [];

        foreach ($lineas as $linea) {
            if (trim($linea) === '') {
                continue;
            }

            $datos = str_getcsv($linea);
            $nombre    = trim($datos[$idxNombre] ?? '');
            $email     = strtolower(trim($datos[$idxEmail] ?? ''));
            $matricula = strtoupper(trim($datos[$idxMatricula] ?? ''));
            $numFila   = count($filas) + count($errores) + 1;

            if (count($filas) >= 200) {
                $errores[] = ['fila' => $numFila, 'nombre' => $nombre, 'email' => $email, 'matricula' => $matricula, 'motivo' => 'Límite de 200 alumnos por importación (filas adicionales ignoradas).'];
                break;
            }

            if ($nombre === '' || $email === '' || $matricula === '') {
                $errores[] = ['fila' => $numFila, 'nombre' => $nombre, 'email' => $email, 'matricula' => $matricula, 'motivo' => 'Falta nombre, email o matrícula.'];
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = ['fila' => $numFila, 'nombre' => $nombre, 'email' => $email, 'matricula' => $matricula, 'motivo' => 'Email inválido.'];
                continue;
            }

            if (isset($vistos[$email])) {
                $errores[] = ['fila' => $numFila, 'nombre' => $nombre, 'email' => $email, 'matricula' => $matricula, 'motivo' => 'Email repetido dentro del archivo.'];
                continue;
            }

            if (isset($vistos[$matricula])) {
                $errores[] = ['fila' => $numFila, 'nombre' => $nombre, 'email' => $email, 'matricula' => $matricula, 'motivo' => 'Matrícula repetida dentro del archivo.'];
                continue;
            }

            $vistos[$email] = true;
            $vistos[$matricula] = true;

            $cuentaExiste = User::where('email', $email)->exists();
            $yaInscrito = false;
            if ($cuentaExiste) {
                $usuarioExistente = User::where('email', $email)->first();
                $yaInscrito = Matricula::where('user_id', $usuarioExistente->id)
                    ->where('materia_id', $materia->id)
                    ->where('grupo_id', $materia->grupo_id)
                    ->where('estado', 'aceptado')
                    ->exists();
            }

            $filas[] = [
                'nombre'    => $nombre,
                'email'     => $email,
                'matricula' => $matricula,
                'estado'    => $yaInscrito ? 'ya' : ($cuentaExiste ? 'cuenta' : 'nuevo'),
            ];
        }

        if (empty($filas) && empty($errores)) {
            return back()->with('error', 'El archivo no contiene filas válidas.');
        }

        session(['importar_preview' => [
            'materia_id' => $materia->id,
            'filas'      => $filas,
        ]]);

        return $this->vistaImportar($materia, $filas, $errores);
    }

    // Paso 3: confirmar la importacion (crea cuentas y matriculas aceptadas)
    public function importarStore(Request $request)
    {
        $preview = session('importar_preview');

        if (!$preview || !isset($preview['materia_id'])) {
            return redirect()->route('profesor.materias.importar')
                ->with('error', 'No hay una previsualización activa. Vuelve a cargar el CSV para importar.');
        }

        $profesorId = Auth::id();
        $materia = Materia::with('grupo')->findOrFail($preview['materia_id']);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) $profesorId) {
            return back()->with('error', 'Solo puedes importar alumnos a las asignaturas que tú impartes.');
        }

        $cuentasCreadas = 0;
        $inscritasExistentes = 0;
        $omitidos = 0;
        $detalleErrores = [];

        foreach ($preview['filas'] as $fila) {
            $usuario = User::where('email', $fila['email'])->first();

            if (!$usuario) {
                $matriculaOcupada = User::where('matricula_codigo', $fila['matricula'])->exists();
                if ($matriculaOcupada) {
                    $omitidos++;
                    $detalleErrores[] = $fila['nombre'] . ' (' . $fila['email'] . '): la matrícula ' . $fila['matricula'] . ' ya pertenece a otra cuenta.';
                    continue;
                }

                $usuario = User::create([
                    'name'             => $fila['nombre'],
                    'email'            => $fila['email'],
                    'rol'              => 'alumno',
                    'matricula_codigo' => $fila['matricula'],
                    'password'         => Hash::make($fila['matricula']),
                ]);
                $cuentasCreadas++;
            }

            if ($usuario->rol !== 'alumno') {
                $omitidos++;
                $detalleErrores[] = $fila['email'] . ' pertenece a una cuenta de ' . $usuario->rol . ', no se inscribió.';
                continue;
            }

            $matricula = Matricula::firstOrCreate(
                [
                    'user_id'    => $usuario->id,
                    'grupo_id'   => $materia->grupo_id,
                    'materia_id' => $materia->id,
                ],
                ['estado' => 'aceptado']
            );

            if ($matricula->estado !== 'aceptado') {
                $matricula->update(['estado' => 'aceptado']);
            }

            if ($matricula->wasRecentlyCreated) {
                $inscritasExistentes++;
            }
        }

        session()->forget('importar_preview');

        $mensaje = "Importación completada en {$materia->nombre_materia}: {$cuentasCreadas} cuentas nuevas (contraseña inicial = su matrícula), {$inscritasExistentes} cuentas existentes inscritas, {$omitidos} omitidos.";
        if (!empty($detalleErrores)) {
            $mensaje .= ' Detalles: ' . implode(' | ', array_slice($detalleErrores, 0, 3));
        }

        return redirect()->route('profesor.materias.importar')->with('success', $mensaje);
    }

    // Vista compartida del flujo de importacion (formulario + previsualizacion)
    private function vistaImportar(Materia $materia, array $filas, array $errores)
    {
        return view('materias.importar', [
            'arbol'   => $this->arbolDocente(),
            'materia' => $materia,
            'filas'   => $filas,
            'errores' => $errores,
        ]);
    }
}
