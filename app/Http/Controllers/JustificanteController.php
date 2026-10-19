<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Justificante;
use App\Models\RegistroAsistencia;

class JustificanteController extends Controller
{
    // 1. Vista del Alumno: Ver sus faltas/retardos y subir justificante (RF-15)
    public function indexAlumno()
    {
        $alumnoId = Auth::id();

        // Obtenemos las faltas o retardos del alumno (sin pedir 'sesion.grupo')
        $incidencias = RegistroAsistencia::with(['sesion.materia', 'justificante'])
            ->where('user_id', $alumnoId)
            ->whereIn('estado_incidencia', ['falta', 'retardo'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Historial de justificantes enviados por el alumno
        $misJustificantes = Justificante::with(['registroAsistencia.sesion.materia'])
            ->whereHas('registroAsistencia', function ($q) use ($alumnoId) {
                $q->where('user_id', $alumnoId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('alumno.justificantes', compact('incidencias', 'misJustificantes'));
    }

    // 2. Guardar el justificante subido por el Alumno
    public function store(Request $request)
    {
        $request->validate([
            'registro_id' => 'required|exists:registro_asistencias,id',
            'motivo'      => 'required|string|max:500',
            'documento'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Máx 5MB
        ]);

        // Guardamos el archivo en storage/app/public/justificantes
        $rutaArchivo = $request->file('documento')->store('justificantes', 'public');

        Justificante::updateOrCreate(
            ['registro_id' => $request->registro_id],
            [
                'motivo'            => $request->motivo,
                'documento_url'     => $rutaArchivo,
                'estado_aprobacion' => 'pendiente',
            ]
        );

        return redirect()->back()->with('success', 'Justificante enviado correctamente para revisión del profesor.');
    }

    // 3. Vista del Profesor: Revisar justificantes de sus materias (RF-16)
    public function indexProfesor()
    {
        $profesorId = Auth::id();

        $justificantes = Justificante::with([
                'registroAsistencia.alumno',
                'registroAsistencia.sesion.materia'
            ])
            ->whereHas('registroAsistencia.sesion', function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId);
            })
            ->orderByRaw("FIELD(estado_aprobacion, 'pendiente', 'aceptado', 'rechazado')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profesor.justificantes', compact('justificantes'));
    }

    // 4. Profesor aprueba o rechaza el justificante
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado_aprobacion' => 'required|in:aceptado,rechazado'
        ]);

        $justificante = Justificante::with('registroAsistencia')->findOrFail($id);
        $justificante->estado_aprobacion = $request->estado_aprobacion;
        $justificante->save();

        // Si el profesor lo acepta, cambiamos automáticamente la falta/retardo a 'permiso'
        if ($request->estado_aprobacion === 'aceptado') {
            $registro = $justificante->registroAsistencia;
            $registro->estado_incidencia = 'permiso';
            $registro->observaciones = 'Justificado: ' . $justificante->motivo;
            $registro->save();
        }

        return redirect()->back()->with('success', 'El estado del justificante ha sido actualizado.');
    }
}