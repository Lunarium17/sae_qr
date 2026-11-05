<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CodigoDocente;

class AuthController extends Controller
{
    // Mostrar vista del login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Mostrar vista de registro de nueva cuenta
    public function showRegister()
    {
        return view('auth.register');
    }

    // Procesar el registro de un nuevo usuario (Alumno o Profesor)
    public function register(Request $request)
    {
        $esAlumno = $request->input('rol') === 'alumno';
        $esProfesor = $request->input('rol') === 'profesor';

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|max:150|unique:users,email',
            'rol'              => 'required|in:alumno,profesor',
            'matricula_codigo' => [
                $esAlumno ? 'required' : 'nullable',
                'string',
                'max:30',
                'unique:users,matricula_codigo',
            ],
            'codigo_docente'   => [
                $esProfesor ? 'required' : 'nullable',
                'string',
                'max:30',
            ],
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'name.required'             => 'El nombre completo es obligatorio.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.email'               => 'Ingresa un correo electrónico válido.',
            'email.unique'              => 'Este correo electrónico ya está registrado en el sistema.',
            'rol.required'              => 'Debes seleccionar el tipo de cuenta (Alumno o Profesor).',
            'rol.in'                    => 'El rol seleccionado no es válido.',
            'matricula_codigo.required' => 'La matrícula escolar es obligatoria para los alumnos.',
            'matricula_codigo.unique'   => 'Esta matrícula o clave ya se encuentra registrada.',
            'codigo_docente.required'   => 'Debes ingresar el Código de Acceso Docente proporcionado por el Administrador.',
            'password.required'         => 'Debes ingresar una contraseña.',
            'password.min'              => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'        => 'La confirmación de la contraseña no coincide.',
        ]);

        $codigoIngresado = $esProfesor ? strtoupper(trim((string) $request->input('codigo_docente'))) : null;

        try {
            DB::transaction(function () use ($request, $esProfesor, $codigoIngresado) {
                $codigoDocenteModel = null;

                // Validar y bloquear (FOR UPDATE) el Código de Invitación Docente para evitar condiciones de carrera (Race Condition)
                if ($esProfesor) {
                    $codigoDocenteModel = CodigoDocente::where('codigo', $codigoIngresado)
                        ->lockForUpdate()
                        ->first();

                    if (!$codigoDocenteModel) {
                        throw new \RuntimeException('CODIGO_NO_EXISTE');
                    }

                    if ($codigoDocenteModel->usado) {
                        throw new \RuntimeException('CODIGO_USADO');
                    }
                }

                $nuevoUsuario = User::create([
                    'name'             => $request->name,
                    'email'            => $request->email,
                    'rol'              => $request->rol,
                    'matricula_codigo' => $request->matricula_codigo ?: null,
                    'password'         => Hash::make($request->password),
                ]);

                if ($codigoDocenteModel) {
                    $codigoDocenteModel->update([
                        'usado'        => true,
                        'usado_por_id' => $nuevoUsuario->id,
                        'usado_en'     => now(),
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            $mensajeError = $e->getMessage() === 'CODIGO_USADO'
                ? 'Este Código de Acceso Docente ya fue utilizado anteriormente. Solicita un nuevo código al Administrador.'
                : 'El Código de Acceso Docente ingresado no existe. Solicita uno válido al Administrador Escolar.';

            return back()
                ->withInput($request->except('password', 'password_confirmation', 'codigo_docente'))
                ->withErrors(['codigo_docente' => $mensajeError]);
        }

        return redirect()->route('login')
            ->with('success', '¡Cuenta creada exitosamente! Ingresa tu correo y contraseña para iniciar sesión.');
    }

    // Procesar el login (inicio de sesión)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            // Redirección basada en el rol (RF-01)
            if ($user->rol === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->rol === 'profesor') {
                return redirect()->intended('/profesor/dashboard');
            } else {
                return redirect()->intended('/alumno/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
