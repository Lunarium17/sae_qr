<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\User;
use App\Models\RegistroAsistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    // Mostrar la lista de asignaturas que imparte el profesor autenticado
    public function index()
    {
        $profesorId = Auth::id();
        $materias = Materia::with(['grupo', 'profesor'])
            ->where(function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId)
                  ->orWhereNull('profesor_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('materias.index', compact('materias'));
    }

    // Mostrar el formulario para crear una nueva materia
    public function create()
    {
        return view('materias.create');
    }

    // Guardar la nueva asignatura en la BD vinculada al grupo y al profesor que la imparte
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
            'profesor_id'    => Auth::id(),
            'nombre_materia' => $request->nombre_materia,
            'codigo_materia' => $request->codigo,
        ]);

        return redirect()->route('grupos.show', $request->grupo_id)
                         ->with('success', 'Asignatura registrada y asignada a tu carga docente dentro del grupo exitosamente.');
    }

    /**
     * Vista detallada de una materia con sus alumnos inscritos (en ese grupo y materia) y estadísticas de asistencia.
     */
    public function show(string $id)
    {
        $profesorId = Auth::id();

        $materia = Materia::with([
            'grupo',
            'profesor',
            'matriculas' => function ($q) {
                $q->with('alumno')
                  ->orderByRaw("FIELD(estado, 'aceptado', 'pendiente', 'rechazado')")
                  ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) $profesorId) {
            return redirect()->route('grupos.show', $materia->grupo_id)
                ->with('error', 'Solo el docente titular de esta asignatura puede gestionar su lista de alumnos.');
        }

        // Estadísticas de asistencia por alumno en esta combinación exacta (materia_id + grupo_id)
        $registrosMateria = RegistroAsistencia::whereHas('sesion', function ($q) use ($materia) {
            $q->where('materia_id', $materia->id);
            if ($materia->grupo_id) {
                $q->where(function ($sub) use ($materia) {
                    $sub->where('grupo_id', $materia->grupo_id)
                        ->orWhereNull('grupo_id');
                });
            }
        })->get()->groupBy('user_id');

        return view('materias.show', compact('materia', 'registrosMateria'));
    }

    /**
     * Mostrar el formulario para editar una asignatura propia.
     */
    public function edit(string $id)
    {
        $materia = Materia::with('grupo')->findOrFail($id);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) Auth::id()) {
            return redirect()->route('grupos.show', $materia->grupo_id)
                ->with('error', 'No tienes permiso para editar una asignatura impartida por otro docente.');
        }

        return view('materias.edit', compact('materia'));
    }

    /**
     * Actualizar una asignatura propia.
     */
    public function update(Request $request, string $id)
    {
        $materia = Materia::findOrFail($id);

        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) Auth::id()) {
            return redirect()->route('grupos.show', $materia->grupo_id)
                ->with('error', 'No tienes permiso para modificar una asignatura impartida por otro docente.');
        }

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
            'profesor_id'    => $materia->profesor_id ?? Auth::id(),
            'nombre_materia' => $request->nombre_materia,
            'codigo_materia' => $request->codigo,
        ]);

        if ($materia->grupo_id) {
            return redirect()->route('grupos.show', $materia->grupo_id)
                             ->with('success', 'Materia actualizada correctamente.');
        }

        return redirect()->route('grupos.index')->with('success', 'Materia actualizada correctamente.');
    }

    // Eliminar una asignatura propia de la BD
    public function destroy(Materia $materia)
    {
        if (!is_null($materia->profesor_id) && (int) $materia->profesor_id !== (int) Auth::id()) {
            return redirect()->route('grupos.show', $materia->grupo_id)
                ->with('error', 'No tienes permiso para eliminar una asignatura impartida por otro docente.');
        }

        $grupoId = $materia->grupo_id;
        $materia->delete();

        if ($grupoId) {
            return redirect()->route('grupos.show', $grupoId)
                             ->with('success', 'Asignatura eliminada del grupo correctamente.');
        }

        return redirect()->route('grupos.index')->with('success', 'Asignatura eliminada correctamente.');
    }
}
