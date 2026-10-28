<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SesionClase;
use App\Models\Matricula;
use App\Models\RegistroAsistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class QrController extends Controller
{
    /**
     * 1. El Alumno genera su Token QR firmado de forma compacta (Expira en 60 segundos).
     * Se utiliza una firma HMAC-SHA256 compacta (~32 caracteres) en lugar de un payload
     * largo de ~240 caracteres para que el código QR tenga cuadros grandes y de alto contraste,
     * permitiendo que cualquier cámara web o de celular lo enfoque y lea al instante.
     */
    public function generarTokenQR()
    {
        $alumno = Auth::user();
        $exp = Carbon::now()->timestamp + 60; // Vigencia estricta de 60 segundos

        $datosBase = $alumno->id . '.' . $exp;
        $firma = substr(hash_hmac('sha256', $datosBase, (string) config('app.key')), 0, 16);
        $token = $datosBase . '.' . $firma;

        return response()->json([
            'status'   => 'success',
            'qr_token' => $token,
            'alumno'   => $alumno->name,
            'mensaje'  => 'Código QR actualizado (Válido por 60 segundos)'
        ]);
    }

    /**
     * 2. El Profesor escanea y valida el QR verificando vigencia, sesión activa y matrícula en (GrupoID + MateriaID).
     */
    public function validarEscaneo(Request $request)
    {
        $request->validate([
            'qr_token'        => 'required|string',
            'sesion_clase_id' => 'required|exists:sesion_clases,id',
        ]);

        try {
            $rawToken = trim((string) $request->qr_token);
            $userId = null;
            $exp = null;

            // Formato compacto HMAC: "{user_id}.{exp}.{firma16}"
            if (preg_match('/^(\d+)\.(\d+)\.([a-f0-9]{16})$/', $rawToken, $matches)) {
                $uid = (int) $matches[1];
                $timestampExp = (int) $matches[2];
                $firmaRecibida = $matches[3];

                $firmaEsperada = substr(hash_hmac('sha256', $uid . '.' . $timestampExp, (string) config('app.key')), 0, 16);

                if (!hash_equals($firmaEsperada, $firmaRecibida)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Firma de código QR inválida.'
                    ], 400);
                }

                $userId = $uid;
                $exp = $timestampExp;
            } else {
                // Compatibilidad con formato anterior Crypt::encryptString
                $datos = json_decode(Crypt::decryptString($rawToken), true);
                $userId = $datos['user_id'] ?? null;
                $exp = $datos['exp'] ?? null;
            }

            if (!$userId || !$exp) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Formato de código QR no reconocido.'
                ], 400);
            }

            // Regla 1: Verificar vigencia del QR (con 5 segundos de margen por desfase de reloj)
            if (Carbon::now()->timestamp > ($exp + 5)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El código QR ha expirado. Pide al alumno que presione "Renovar QR ahora".'
                ], 401);
            }

            $alumno = User::find($userId);
            if (!$alumno || $alumno->rol !== 'alumno') {
                return response()->json(['status' => 'error', 'message' => 'Alumno no válido.'], 404);
            }

            // Regla 2: Verificar que la sesión de clase esté activa y pertenezca al docente autenticado
            $sesion = SesionClase::with(['grupo', 'materia.grupo'])->find($request->sesion_clase_id);
            if (!$sesion || $sesion->estado !== 'activa') {
                return response()->json(['status' => 'error', 'message' => 'La sesión de clase ya fue cerrada.'], 400);
            }

            if ((int) $sesion->profesor_id !== (int) Auth::id()) {
                return response()->json(['status' => 'error', 'message' => 'Esta sesión pertenece a otro docente.'], 403);
            }

            // Regla 3: Verificar que el alumno esté inscrito y ACEPTADO en la combinación exacta (MateriaID + GrupoID)
            $grupoId = $sesion->grupo_id ?? optional($sesion->materia)->grupo_id;

            $queryMatricula = Matricula::where('user_id', $alumno->id)
                ->where('materia_id', $sesion->materia_id)
                ->where('estado', 'aceptado');

            if ($grupoId) {
                $queryMatricula->where('grupo_id', $grupoId);
            }

            $matriculado = $queryMatricula->exists();

            if (!$matriculado) {
                $nombreGrupo = optional($sesion->grupo)->nombre_grupo ?? optional(optional($sesion->materia)->grupo)->nombre_grupo ?? '';
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El alumno ' . $alumno->name . ' NO está inscrito como Aceptado en esta asignatura' . ($nombreGrupo ? ' (Grupo ' . $nombreGrupo . ')' : '') . '.'
                ], 403);
            }

            // Regla 4: Evitar registros duplicados en la misma sesión
            $duplicado = RegistroAsistencia::where('sesion_clase_id', $sesion->id)
                ->where('user_id', $alumno->id)
                ->exists();

            if ($duplicado) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => $alumno->name . ' ya tiene registrada su asistencia en esta sesión.'
                ], 409);
            }

            // Regla 5: Calcular si es 'asistencia' o 'retardo' según los minutos de tolerancia
            $horaLimiteAsistencia = Carbon::parse($sesion->fecha . ' ' . $sesion->hora_inicio)
                ->addMinutes($sesion->tolerancia_minutos);

            $ahora = Carbon::now();
            $estadoIncidencia = $ahora->greaterThan($horaLimiteAsistencia) ? 'retardo' : 'asistencia';

            // Guardar en la tabla registro_asistencias
            $nuevoRegistro = RegistroAsistencia::create([
                'sesion_clase_id'   => $sesion->id,
                'user_id'           => $alumno->id,
                'timestamp_escaneo' => $ahora,
                'estado_incidencia' => $estadoIncidencia,
                'observaciones'     => 'Registrado por escaneo QR',
            ]);

            return response()->json([
                'status'      => 'success',
                'message'     => '¡Registrado! ' . $alumno->name . ' (' . strtoupper($estadoIncidencia) . ')',
                'registro_id' => $nuevoRegistro->id,
                'alumno'      => $alumno->name,
                'estado'      => $estadoIncidencia,
                'hora'        => $ahora->format('Y-m-d H:i:s'),
                'url_manual'  => route('asistencias.manual', $nuevoRegistro->id),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Código QR inválido o corrupto.'
            ], 400);
        }
    }

    /**
     * 3. Confirmación en Tiempo Real en la Pantalla del Alumno cuando su QR es escaneado.
     * Devuelve el último registro de asistencia del alumno en una sesión activa del día de hoy.
     */
    public function verificarEscaneoReciente()
    {
        $alumnoId = Auth::id();
        $hoy = Carbon::now()->toDateString();

        $registroHoy = RegistroAsistencia::with(['sesion.materia', 'sesion.grupo'])
            ->where('user_id', $alumnoId)
            ->whereNotNull('timestamp_escaneo')
            ->whereHas('sesion', function ($q) use ($hoy) {
                $q->where('fecha', $hoy);
            })
            ->orderByDesc('id')
            ->first();

        if (!$registroHoy || !$registroHoy->sesion) {
            return response()->json([
                'escaneado' => false,
            ]);
        }

        $nombreGrupo = optional($registroHoy->sesion->grupo)->nombre_grupo
            ?? optional(optional($registroHoy->sesion->materia)->grupo)->nombre_grupo
            ?? '';

        return response()->json([
            'escaneado'   => true,
            'registro_id' => $registroHoy->id,
            'materia'     => optional($registroHoy->sesion->materia)->nombre_materia ?? 'Asignatura',
            'grupo'       => $nombreGrupo,
            'estado'      => $registroHoy->estado_incidencia,
            'hora'        => Carbon::parse($registroHoy->timestamp_escaneo)->format('H:i:s'),
        ]);
    }
}
