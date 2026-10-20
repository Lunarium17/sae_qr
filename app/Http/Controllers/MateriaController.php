<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\User;
use App\Models\RegistroAsistencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    // mostrar la lista de materias
    public function index()
    {
        $materias = Materia::with('grupo')->orderBy('created_at', 'desc')->get();
        return view('materias.index', compact('materias'));
    }

    // mostrar el formulario para crear una nueva materia
    public function create()
    {
        return view('materias.create');
    }

    // guardar la nueva materia en la BD vinculada a su grupo (sin duplicados en el mismo grupo)
    public function store(Request $request)
    {
        $request->validate([
            'grupo_id'       => 'required|exists:grupos,id',
            'nombre_materia' => [
                'required',
                'string',
                'max:100',
                Rule::unique('materias', 'nombre_materia')->where(function ($query) use ($request) {
                    return $query->where('grupo_id', $request->grupo_id);
                }),
            ],
            'codigo'         => [
                'required',
                'string',
                'max:30',
                Rule::unique('materias', 'codigo_materia')->where(function ($query) use ($request) {
                    return $query->where('grupo_id', $request->grupo_id);
                }),
            ],
        ], [
            'grupo_id.required'       => 'Debes indicar el grupo al que pertenece la materia.',
            'grupo_id.exists'         => 'El grupo seleccionado no existe.',
            'nombre_materia.required' => 'El nombre de la materia es obligatorio.',
            'nombre_materia.unique'   => 'La materia "' . $request->nombre_materia . '" ya está registrada en este grupo.',
            'codigo.required'         => 'El código o clave de la materia es obligatorio.',
            'codigo.unique'           => 'Ya existe una materia registrada con el código "' . $request->codigo . '" dentro de este grupo.',
        ]);

        Materia::create([
            'grupo_id'       => $request->grupo_id,
            'nombre_materia' => $request->nombre_materia,
            'codigo_materia' => $request->codigo,
        ]);

        return redirect()->route('grupos.show', $request->grupo_id)
                         ->with('success', 'Materia registrada y vinculada al grupo exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    // Vista detallada de una materia con sus alumnos inscritos y estadísticas de asistencia
    public function show(string $id)
    {
        $materia = Materia::with([
            'grupo',
            'matriculas' => function ($q) {
                $q->with('alumno')
                  ->orderByRaw("FIELD(estado, 'aceptado', 'pendiente', 'rechazado')")
                  ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Alumnos registrados en el sistema para poder inscribirlos manualmente
        $alumnosDisponibles = User::where('rol', 'alumno')
            ->orderBy('name')
            ->get();

        // Estadísticas de asistencia por alumno en esta materia
        $registrosMateria = RegistroAsistencia::whereHas('sesion', function ($q) use ($materia) {
            $q->where('materia_id', $materia->id);
        })->get()->groupBy('user_id');

        return view('materias.show', compact('materia', 'alumnosDisponibles', 'registrosMateria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $materia = Materia::with('grupo')->findOrFail($id);
        return view('materias.edit', compact('materia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $materia = Materia::findOrFail($id);
        $grupoId = $request->input('grupo_id', $materia->grupo_id);

        $request->validate([
            'nombre_materia' => [
                'required',
                'string',
                'max:100',
                Rule::unique('materias', 'nombre_materia')
                    ->where(function ($query) use ($grupoId) {
                        return $query->where('grupo_id', $grupoId);
                    })
                    ->ignore($materia->id),
            ],
            'codigo'         => [
                'required',
                'string',
                'max:30',
                Rule::unique('materias', 'codigo_materia')
                    ->where(function ($query) use ($grupoId) {
                        return $query->where('grupo_id', $grupoId);
                    })
                    ->ignore($materia->id),
            ],
        ], [
            'nombre_materia.required' => 'El nombre de la materia es obligatorio.',
            'nombre_materia.unique'   => 'Ya existe otra materia llamada "' . $request->nombre_materia . '" en este grupo.',
            'codigo.required'         => 'El código o clave de la materia es obligatorio.',
            'codigo.unique'           => 'Ya existe otra materia con el código "' . $request->codigo . '" en este grupo.',
        ]);

        $materia->update([
            'nombre_materia' => $request->nombre_materia,
            'codigo_materia' => $request->codigo,
        ]);

        if ($materia->grupo_id) {
            return redirect()->route('grupos.show', $materia->grupo_id)
                             ->with('success', 'Materia actualizada correctamente.');
        }

        return redirect()->route('grupos.index')->with('success', 'Materia actualizada correctamente.');
    }

    // eliminar una materia de la BD
    public function destroy(Materia $materia)
    {
        $grupoId = $materia->grupo_id;
        $materia->delete();

        if ($grupoId) {
            return redirect()->route('grupos.show', $grupoId)
                             ->with('success', 'Materia eliminada del grupo correctamente.');
        }

        return redirect()->route('grupos.index')->with('success', 'Materia eliminada correctamente.');
    }
}
