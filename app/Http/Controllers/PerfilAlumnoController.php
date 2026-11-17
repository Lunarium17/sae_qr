<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PerfilAlumnoController extends Controller
{
    // Mostrar formulario con los datos personales del alumno
    public function edit()
    {
        $alumno = User::findOrFail(Auth::id());
        return view('alumno.perfil', compact('alumno'));
    }

    // Actualizar los datos personales y/o contraseña del alumno
    public function update(Request $request)
    {
        $alumno = User::findOrFail(Auth::id());

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($alumno->id),
            ],
            'matricula_codigo' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'matricula_codigo')->ignore($alumno->id),
            ],
            'password'         => 'nullable|string|min:6|confirmed',
            'foto_perfil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta matrícula o código escolar ya está registrado por otro alumno.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
            'foto_perfil.image'       => 'El archivo seleccionado debe ser una imagen.',
            'foto_perfil.mimes'       => 'La foto debe ser JPG, PNG o WEBP.',
            'foto_perfil.max'         => 'La foto no debe superar los 2 MB.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $foto = $this->guardarFotoPerfil($request, $alumno, $datos);
        if (!is_null($foto)) {
            $datos['foto_perfil'] = $foto;
        }

        $alumno->update($datos);

        return redirect()->route('alumno.perfil')->with('success', 'Tus datos personales se han actualizado correctamente.');
    }

    // Guarda (o elimina) la foto de perfil en el disco público y devuelve la ruta relativa
    private function guardarFotoPerfil(Request $request, User $usuario, array &$datos): ?string
    {
        if ($request->boolean('eliminar_foto')) {
            $this->borrarFotoAnterior($usuario);
            $datos['foto_perfil'] = null;

            return null;
        }

        if (!$request->hasFile('foto_perfil')) {
            return null;
        }

        $archivo = $request->file('foto_perfil');
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'jpg');
        $ruta = 'avatars/user-' . $usuario->id . '.' . $extension;

        $this->borrarFotoAnterior($usuario);
        $archivo->storeAs('avatars', 'user-' . $usuario->id . '.' . $extension, 'public');

        return $ruta;
    }

    private function borrarFotoAnterior(User $usuario): void
    {
        if ($usuario->foto_perfil && Storage::disk('public')->exists($usuario->foto_perfil)) {
            Storage::disk('public')->delete($usuario->foto_perfil);
        }
    }

    // Mostrar formulario con los datos personales del profesor
    public function editProfesor()
    {
        $profesor = User::findOrFail(Auth::id());
        return view('profesor.perfil', compact('profesor'));
    }

    // Actualizar los datos personales y/o contraseña del profesor
    public function updateProfesor(Request $request)
    {
        $profesor = User::findOrFail(Auth::id());

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($profesor->id),
            ],
            'matricula_codigo' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'matricula_codigo')->ignore($profesor->id),
            ],
            'password'         => 'nullable|string|min:6|confirmed',
            'foto_perfil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta clave docente o número de empleado ya está registrado por otro usuario.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
            'foto_perfil.image'       => 'El archivo seleccionado debe ser una imagen.',
            'foto_perfil.mimes'       => 'La foto debe ser JPG, PNG o WEBP.',
            'foto_perfil.max'         => 'La foto no debe superar los 2 MB.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $foto = $this->guardarFotoPerfil($request, $profesor, $datos);
        if (!is_null($foto)) {
            $datos['foto_perfil'] = $foto;
        }

        $profesor->update($datos);

        return redirect()->route('profesor.perfil')->with('success', 'Tus datos de perfil docente se han actualizado correctamente.');
    }

    // Mostrar formulario con los datos personales del administrador
    public function editAdmin()
    {
        $admin = User::findOrFail(Auth::id());
        return view('admin.perfil', compact('admin'));
    }

    // Actualizar los datos personales y/o contraseña del administrador
    public function updateAdmin(Request $request)
    {
        $admin = User::findOrFail(Auth::id());

        $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'password'    => 'nullable|string|min:6|confirmed',
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'     => 'Tu nombre completo es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingresa un correo electrónico válido.',
            'email.unique'      => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.min'      => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'foto_perfil.image' => 'El archivo seleccionado debe ser una imagen.',
            'foto_perfil.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'foto_perfil.max'   => 'La foto no debe superar los 2 MB.',
        ]);

        $datos = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $foto = $this->guardarFotoPerfil($request, $admin, $datos);
        if (!is_null($foto)) {
            $datos['foto_perfil'] = $foto;
        }

        $admin->update($datos);

        return redirect()->route('admin.perfil')->with('success', 'Tus datos de perfil de administrador se han actualizado correctamente.');
    }
}

