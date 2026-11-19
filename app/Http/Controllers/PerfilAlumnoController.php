<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PerfilAlumnoController extends Controller
{
    private function asegurarColumnaFotoPerfil(): void
    {
        if (!Schema::hasColumn('users', 'foto_perfil')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('foto_perfil')->nullable()->after('matricula_codigo');
            });
        }
    }

    // Mostrar formulario con los datos personales del alumno
    public function edit()
    {
        $this->asegurarColumnaFotoPerfil();
        $alumno = User::findOrFail(Auth::id());
        return view('alumno.perfil', compact('alumno'));
    }

    // Actualizar los datos personales, foto y/o contraseña del alumno
    public function update(Request $request)
    {
        $this->asegurarColumnaFotoPerfil();
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
            'foto_perfil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'password'         => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta matrícula o código escolar ya está registrado por otro alumno.',
            'foto_perfil.image'       => 'El archivo de perfil debe ser una imagen válida (JPG, PNG o WEBP).',
            'foto_perfil.max'         => 'La foto de perfil no debe superar los 3 MB.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->hasFile('foto_perfil')) {
            if ($alumno->foto_perfil && Storage::disk('public')->exists($alumno->foto_perfil)) {
                Storage::disk('public')->delete($alumno->foto_perfil);
            }
            $datos['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $alumno->update($datos);

        return redirect()->route('alumno.perfil')->with('success', 'Tus datos personales se han actualizado correctamente.');
    }

    // Mostrar formulario con los datos personales del profesor
    public function editProfesor()
    {
        $this->asegurarColumnaFotoPerfil();
        $profesor = User::findOrFail(Auth::id());
        return view('profesor.perfil', compact('profesor'));
    }

    // Actualizar los datos personales, foto y/o contraseña del profesor
    public function updateProfesor(Request $request)
    {
        $this->asegurarColumnaFotoPerfil();
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
            'foto_perfil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'password'         => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta clave docente o número de empleado ya está registrado por otro usuario.',
            'foto_perfil.image'       => 'El archivo de perfil debe ser una imagen válida (JPG, PNG o WEBP).',
            'foto_perfil.max'         => 'La foto de perfil no debe superar los 3 MB.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->hasFile('foto_perfil')) {
            if ($profesor->foto_perfil && Storage::disk('public')->exists($profesor->foto_perfil)) {
                Storage::disk('public')->delete($profesor->foto_perfil);
            }
            $datos['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $profesor->update($datos);

        return redirect()->route('profesor.perfil')->with('success', 'Tus datos de perfil docente se han actualizado correctamente.');
    }

    // Mostrar formulario con los datos personales del administrador
    public function editAdmin()
    {
        $this->asegurarColumnaFotoPerfil();
        $admin = User::findOrFail(Auth::id());
        return view('admin.perfil', compact('admin'));
    }

    // Actualizar los datos personales, foto y/o contraseña del administrador
    public function updateAdmin(Request $request)
    {
        $this->asegurarColumnaFotoPerfil();
        $admin = User::findOrFail(Auth::id());

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'matricula_codigo' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'matricula_codigo')->ignore($admin->id),
            ],
            'foto_perfil'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'password'         => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta clave administrativa ya está registrada por otro usuario.',
            'foto_perfil.image'       => 'El archivo de perfil debe ser una imagen válida (JPG, PNG o WEBP).',
            'foto_perfil.max'         => 'La foto de perfil no debe superar los 3 MB.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->hasFile('foto_perfil')) {
            if ($admin->foto_perfil && Storage::disk('public')->exists($admin->foto_perfil)) {
                Storage::disk('public')->delete($admin->foto_perfil);
            }
            $datos['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $admin->update($datos);

        return redirect()->route('admin.perfil')->with('success', 'Tus datos de perfil de administrador se han actualizado correctamente.');
    }
}

