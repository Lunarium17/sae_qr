<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\CodigoDocente;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\RegistroAsistencia;
use App\Models\SesionClase;

class AdminController extends Controller
{
    /**
     * Panel principal del Administrador Escolar (sin tabla de Alerta Escolar).
     */
    public function dashboard()
    {
        $totalAlumnos    = User::where('rol', 'alumno')->count();
        $totalProfesores = User::where('rol', 'profesor')->count();
        $totalAdmins     = User::where('rol', 'admin')->count();

        $grupos        = Grupo::with('materias')->orderBy('nombre_grupo')->get();
        $totalGrupos   = $grupos->count();
        $totalMaterias = Materia::count();
        $totalSesiones = SesionClase::count();

        $codigosDisponiblesCount = CodigoDocente::where('usado', false)->count();
        $codigosUsadosCount      = CodigoDocente::where('usado', true)->count();

        $asistenciasCount = RegistroAsistencia::where('estado_incidencia', 'asistencia')->count();
        $retardosCount    = RegistroAsistencia::where('estado_incidencia', 'retardo')->count();
        $permisosCount    = RegistroAsistencia::where('estado_incidencia', 'permiso')->count();
        $faltasCount      = RegistroAsistencia::where('estado_incidencia', 'falta')->count();

        $totalRegistros       = $asistenciasCount + $retardosCount + $permisosCount + $faltasCount;
        $asistenciasEfectivas = $asistenciasCount + $retardosCount + $permisosCount;
        $porcentajeAsistencia = $totalRegistros > 0 ? round(($asistenciasEfectivas / $totalRegistros) * 100) : 0;

        $colorSemaforoGlobal = $porcentajeAsistencia >= 85 ? 'success' : ($porcentajeAsistencia >= 70 ? 'warning' : 'danger');

        $codigosRecientes = CodigoDocente::with(['usadoPor', 'creadoPor'])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalAlumnos',
            'totalProfesores',
            'totalAdmins',
            'grupos',
            'totalGrupos',
            'totalMaterias',
            'totalSesiones',
            'codigosDisponiblesCount',
            'codigosUsadosCount',
            'asistenciasCount',
            'retardosCount',
            'permisosCount',
            'faltasCount',
            'totalRegistros',
            'porcentajeAsistencia',
            'colorSemaforoGlobal',
            'codigosRecientes'
        ));
    }

    /**
     * Vista de gestión de Códigos de Acceso Docente (de un solo uso).
     */
    public function codigosDocentes()
    {
        $codigos = CodigoDocente::with(['usadoPor', 'creadoPor'])
            ->orderBy('usado', 'asc')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.codigos', compact('codigos'));
    }

    /**
     * Generar un nuevo Código de Acceso Docente único.
     */
    public function generarCodigoDocente(Request $request)
    {
        $request->validate([
            'descripcion_nota'     => 'nullable|string|max:150',
            'codigo_personalizado' => 'nullable|string|max:30',
        ], [
            'descripcion_nota.max'     => 'La nota o referencia no debe exceder 150 caracteres.',
            'codigo_personalizado.max' => 'El código personalizado no debe exceder 30 caracteres.',
        ]);

        $codigoPersonalizado = strtoupper(trim((string) $request->input('codigo_personalizado')));

        if ($codigoPersonalizado !== '') {
            if (CodigoDocente::where('codigo', $codigoPersonalizado)->exists()) {
                return back()
                    ->withInput()
                    ->withErrors(['codigo_personalizado' => 'Ese código ya existe en el sistema. Elige otro o déjalo en blanco para generarlo automáticamente.']);
            }
            $codigoFinal = $codigoPersonalizado;
        } else {
            do {
                $codigoFinal = 'DOC-' . Str::upper(Str::random(4)) . '-' . date('y');
            } while (CodigoDocente::where('codigo', $codigoFinal)->exists());
        }

        CodigoDocente::create([
            'codigo'           => $codigoFinal,
            'descripcion_nota' => $request->input('descripcion_nota') ?: null,
            'usado'            => false,
            'creado_por_id'    => Auth::id(),
        ]);

        return back()->with('success', "¡Código docente generado exitosamente! Código: {$codigoFinal}");
    }

    /**
     * Eliminar o revocar un Código de Acceso Docente.
     */
    public function eliminarCodigoDocente($id)
    {
        $codigo = CodigoDocente::findOrFail($id);
        $textoCodigo = $codigo->codigo;
        $codigo->delete();

        return back()->with('success', "El código {$textoCodigo} fue eliminado correctamente.");
    }

    /**
     * Directorio y gestión de usuarios (Profesores, Alumnos y Administradores).
     */
    public function usuarios(Request $request)
    {
        $rolFiltro = $request->input('rol', 'todos');
        $buscar    = trim((string) $request->input('buscar', ''));

        $query = User::query();

        if (in_array($rolFiltro, ['admin', 'profesor', 'alumno'], true)) {
            $query->where('rol', $rolFiltro);
        }

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('matricula_codigo', 'like', "%{$buscar}%");
            });
        }

        $usuarios = $query->orderBy('rol')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.usuarios', compact('usuarios', 'rolFiltro', 'buscar'));
    }

    /**
     * Crear un usuario directamente desde el panel de Administrador.
     */
    public function storeUsuario(Request $request)
    {
        $esAlumno = $request->input('rol') === 'alumno';

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|max:150|unique:users,email',
            'rol'              => 'required|in:admin,profesor,alumno',
            'matricula_codigo' => [
                $esAlumno ? 'required' : 'nullable',
                'string',
                'max:30',
                'unique:users,matricula_codigo',
            ],
            'password'         => 'required|string|min:6',
        ], [
            'name.required'             => 'El nombre completo es obligatorio.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.email'               => 'Ingresa un correo electrónico válido.',
            'email.unique'              => 'Este correo electrónico ya está registrado.',
            'rol.required'              => 'Selecciona el rol del usuario.',
            'matricula_codigo.required' => 'La matrícula escolar es obligatoria para alumnos.',
            'matricula_codigo.unique'   => 'Esta matrícula o clave ya pertenece a otro usuario.',
            'password.required'         => 'Debes asignar una contraseña inicial.',
            'password.min'              => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'rol'              => $request->rol,
            'matricula_codigo' => $request->matricula_codigo ?: null,
            'password'         => Hash::make($request->password),
        ]);

        return back()->with('success', "Usuario {$request->name} ({$request->rol}) creado correctamente.");
    }

    /**
     * Actualizar datos o restablecer contraseña de un usuario.
     */
    public function updateUsuario(Request $request, $id)
    {
        $usuario  = User::findOrFail($id);
        $esAlumno = $request->input('rol') === 'alumno';

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($usuario->id),
            ],
            'rol'              => 'required|in:admin,profesor,alumno',
            'matricula_codigo' => [
                $esAlumno ? 'required' : 'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'matricula_codigo')->ignore($usuario->id),
            ],
            'password'         => 'nullable|string|min:6',
        ], [
            'name.required'             => 'El nombre completo es obligatorio.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.unique'              => 'Este correo electrónico ya está en uso por otra cuenta.',
            'matricula_codigo.required' => 'La matrícula escolar es obligatoria para los alumnos.',
            'matricula_codigo.unique'   => 'Esta matrícula o clave ya está registrada por otro usuario.',
            'password.min'              => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ]);

        // Evitar que el admin autenticado se quite a sí mismo el rol de admin por accidente
        if ($usuario->id === Auth::id() && $request->rol !== 'admin') {
            return back()->withErrors(['rol' => 'No puedes cambiar el rol de tu propia cuenta de Administrador activa.']);
        }

        $usuario->name             = $request->name;
        $usuario->email            = $request->email;
        $usuario->rol              = $request->rol;
        $usuario->matricula_codigo = $request->matricula_codigo ?: null;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        $mensaje = $request->filled('password')
            ? "Datos y contraseña de {$usuario->name} actualizados correctamente."
            : "Datos de {$usuario->name} actualizados correctamente.";

        return back()->with('success', $mensaje);
    }

    /**
     * Eliminar cuenta de usuario.
     */
    public function destroyUsuario($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta de Administrador en sesión.']);
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return back()->with('success', "La cuenta de {$nombre} ha sido eliminada del sistema.");
    }

    /**
     * Crear un Grupo Oficial Universitario desde el Panel de Administrador.
     */
    public function storeGrupo(Request $request)
    {
        $request->validate([
            'nombre_grupo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grupos', 'nombre_grupo')->where(function ($query) use ($request) {
                    return $query->where('turno', $request->turno);
                }),
            ],
            'carrera'         => 'required|string|max:120',
            'grado'           => 'required|string|max:50',
            'turno'           => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ], [
            'nombre_grupo.required'    => 'El nombre del grupo oficial es obligatorio.',
            'nombre_grupo.unique'      => 'Ya existe el grupo "' . $request->nombre_grupo . '" en el turno ' . $request->turno . '.',
            'carrera.required'         => 'La carrera o programa educativo es obligatorio.',
            'grado.required'           => 'El semestre es obligatorio.',
            'turno.required'           => 'Debes seleccionar un turno.',
            'periodo_lectivo.required' => 'El periodo lectivo es obligatorio.',
        ]);

        Grupo::create([
            'nombre_grupo'    => trim($request->nombre_grupo),
            'carrera'         => trim($request->carrera),
            'grado'           => $request->grado,
            'turno'           => $request->turno,
            'periodo_lectivo' => trim($request->periodo_lectivo),
        ]);

        return back()->with('success', "Grupo oficial {$request->nombre_grupo} ({$request->carrera}) creado exitosamente.");
    }

    /**
     * Actualizar un Grupo Oficial Universitario desde el Panel de Administrador.
     */
    public function updateGrupo(Request $request, $id)
    {
        $grupo = Grupo::findOrFail($id);

        $request->validate([
            'nombre_grupo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grupos', 'nombre_grupo')
                    ->where(function ($query) use ($request) {
                        return $query->where('turno', $request->turno);
                    })
                    ->ignore($grupo->id),
            ],
            'carrera'         => 'required|string|max:120',
            'grado'           => 'required|string|max:50',
            'turno'           => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ], [
            'nombre_grupo.required'    => 'El nombre del grupo oficial es obligatorio.',
            'nombre_grupo.unique'      => 'Ya existe otro grupo "' . $request->nombre_grupo . '" en el turno ' . $request->turno . '.',
            'carrera.required'         => 'La carrera o programa educativo es obligatorio.',
            'grado.required'           => 'El semestre es obligatorio.',
            'turno.required'           => 'Debes seleccionar un turno.',
            'periodo_lectivo.required' => 'El periodo lectivo es obligatorio.',
        ]);

        $grupo->update([
            'nombre_grupo'    => trim($request->nombre_grupo),
            'carrera'         => trim($request->carrera),
            'grado'           => $request->grado,
            'turno'           => $request->turno,
            'periodo_lectivo' => trim($request->periodo_lectivo),
        ]);

        return back()->with('success', "Grupo oficial {$grupo->nombre_grupo} actualizado correctamente.");
    }

    /**
     * Eliminar un Grupo Oficial desde el Panel de Administrador.
     */
    public function destroyGrupo($id)
    {
        $grupo = Grupo::findOrFail($id);
        $nombre = $grupo->nombre_grupo;
        $grupo->delete();

        return back()->with('success', "El grupo oficial {$nombre} fue eliminado del catálogo institucional.");
    }
}

