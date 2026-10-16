<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController extends Controller
{

    // mostrar la lista de materias
    public function index()
    {
        $materias = Materia::orderBy('created_at', 'desc')->get();
        return view('materias.index', compact('materias'));
    }

    // mostrar el formulario para crear una nueva materia
    public function create()
    {
        return view('materias.create');
    }

    // guardar la nueva materia en la BD
    public function store(Request $request)
    {
        $request->validate([
            'nombre_materia' => 'required|string|max:100',
            'codigo' => 'required|string|max:30|unique:materias,codigo_materia', // valida codifo materia
        ]);

        Materia::create([
            'nombre_materia' => $request->nombre_materia,
            'codigo_materia' => $request->codigo, // guarda el input del campo 'codigo' en la columna 'codigo_materia' de la tabla 'materias'
        ]);

        return redirect()->route('materias.index')->with('success', 'Materia registrada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    // eliminar una materia de la BD
    public function destroy(Materia $materia)
    {
        $materia->delete();

        return redirect()->route('materias.index')->with('success', 'Materia eliminada correctamente.');
    }
}
