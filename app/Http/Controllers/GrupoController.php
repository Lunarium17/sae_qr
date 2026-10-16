<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;


class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // mostar la lista de grupos 
    public function index()
    {
        $grupos = Grupo::orderBy('created_at', 'desc')->get();
        return view('grupos.index', compact('grupos'));
    }

    /**
     * Show the form for creating a new resource.
     */

    // mostrar el formulario para crear un nuevo grupo
    public function create()
    {
        return view('grupos.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    // guardar el nuevo grupos en la BD
    public function store(Request $request)
    {
        // 1. Validar los datos recibidos del formulario
        $request->validate([
            'nombre' => 'required|string|max:50',
            'grado' => 'required|string|max:50',
            'turno' => 'required|string|max:30',
            'periodo_lectivo' => 'required|string|max:50',
        ]);

        // 2. Crear el registro en la base de datos (Modelo Grupo)
        Grupo::create([
            'nombre_grupo' => $request->nombre,
            'grado' => $request->grado,
            'turno' => $request->turno,
            'periodo_lectivo' => $request->periodo_lectivo,
        ]);

        // 3. Redirigir de vuelta al índice de grupos con un mensaje de éxito
        return redirect()->route('grupos.index')->with('success', 'Grupo creado exitosamente.');
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

    /**
     * Remove the specified resource from storage.
     */

    // eliminar un grupo de la BD
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado correctamente.');
    }
}
