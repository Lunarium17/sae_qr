<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Justificante;
use App\Models\RegistroAsistencia;

class JustificanteController extends Controller
{
    // 1. Vista del Alumno: Ver sus faltas/retardos y subir justificante (RF-15)
    public function indexAlumno(Request $request)
    {
        $alumnoId = Auth::id();
        $registroSeleccionado = $request->query('registro_id');

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

        return view('alumno.justificantes', compact('incidencias', 'misJustificantes', 'registroSeleccionado'));
    }

    // 2. Guardar el justificante subido por el Alumno (con validación anti-IDOR de pertenencia)
    public function store(Request $request)
    {
        $alumnoId = Auth::id();

        $request->validate([
            'registro_id' => [
                'required',
                Rule::exists('registro_asistencias', 'id')->where(function ($query) use ($alumnoId) {
                    return $query->where('user_id', $alumnoId)
                                 ->whereIn('estado_incidencia', ['falta', 'retardo']);
                }),
            ],
            'motivo'      => 'required|string|max:500',
            'documento'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Opcional, máx 5MB
        ], [
            'registro_id.required' => 'Debes seleccionar la sesión o incidencia que deseas justificar.',
            'registro_id.exists'   => 'La incidencia seleccionada no es válida o no pertenece a tu historial.',
            'motivo.required'      => 'Debes escribir el motivo de la inasistencia o retardo.',
            'motivo.max'           => 'El motivo no puede exceder los 500 caracteres.',
            'documento.file'       => 'El archivo subido no es válido.',
            'documento.mimes'      => 'El justificante solo puede ser un archivo en formato PDF, JPG, JPEG o PNG.',
            'documento.max'        => 'El archivo del justificante no debe superar el tamaño máximo de 5 MB.',
            'documento.uploaded'   => 'No se pudo subir el archivo. Verifica que sea formato PDF, JPG, JPEG o PNG y que no supere los 5 MB.',
        ]);

        // Guardamos el archivo en storage/app/public/justificantes si se adjuntó uno
        $rutaArchivo = $request->hasFile('documento')
            ? $request->file('documento')->store('justificantes', 'public')
            : null;

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

    // 4. Profesor aprueba o rechaza el justificante (verificando que la sesión pertenezca al profesor)
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado_aprobacion' => 'required|in:aceptado,rechazado'
        ]);

        $profesorId = Auth::id();
        $justificante = Justificante::with('registroAsistencia.sesion')
            ->whereHas('registroAsistencia.sesion', function ($q) use ($profesorId) {
                $q->where('profesor_id', $profesorId);
            })
            ->findOrFail($id);

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