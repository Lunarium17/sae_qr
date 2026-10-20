<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta matrícula o código escolar ya está registrado por otro alumno.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $alumno->update($datos);

        return redirect()->route('alumno.perfil')->with('success', 'Tus datos personales se han actualizado correctamente.');
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
        ], [
            'name.required'           => 'Tu nombre completo es obligatorio.',
            'email.required'          => 'El correo electrónico es obligatorio.',
            'email.email'             => 'Ingresa un correo electrónico válido.',
            'email.unique'            => 'Este correo electrónico ya está registrado por otro usuario.',
            'matricula_codigo.unique' => 'Esta clave docente o número de empleado ya está registrado por otro usuario.',
            'password.min'            => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'      => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $datos = [
            'name'             => $request->name,
            'email'            => $request->email,
            'matricula_codigo' => $request->matricula_codigo,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $profesor->update($datos);

        return redirect()->route('profesor.perfil')->with('success', 'Tus datos de perfil docente se han actualizado correctamente.');
    }
}

