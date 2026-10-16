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
    // 1. El Alumno genera su Token QR encriptado (Expira en 60 segundos)
    public function generarTokenQR()
    {
        $alumno = Auth::user();

        $payload = [
            'user_id' => $alumno->id,
            'exp' => Carbon::now()->timestamp + 60, // Vigencia estricta de 60 segundos
        ];

        // Encriptamos el payload con la firma de seguridad de Laravel
        $token = Crypt::encryptString(json_encode($payload));

        return response()->json([
            'status' => 'success',
            'qr_token' => $token,
            'alumno' => $alumno->name,
            'mensaje' => 'Código QR actualizado (Válido por 60 segundos)'
        ]);
    }

    // 2. El Profesor escanea y valida el QR contra MariaDB
    public function validarEscaneo(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'sesion_clase_id' => 'required|exists:sesion_clases,id',
        ]);

        try {
            // Decodificar el token encriptado
            $datos = json_decode(Crypt::decryptString($request->qr_token), true);

            // Regla 1: Verificar vigencia del QR (60 segundos)
            if (Carbon::now()->timestamp > $datos['exp']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El código QR ha expirado. Pide al alumno que actualice su pantalla.'
                ], 401);
            }

            $alumno = User::find($datos['user_id']);
            if (!$alumno || $alumno->rol !== 'alumno') {
                return response()->json(['status' => 'error', 'message' => 'Alumno no válido.'], 404);
            }

            // Regla 2: Verificar que la sesión de clase esté activa
            $sesion = SesionClase::find($request->sesion_clase_id);
            if ($sesion->estado !== 'activa') {
                return response()->json(['status' => 'error', 'message' => 'La sesión de clase ya fue cerrada.'], 400);
            }

            // Regla 3: Verificar que el alumno esté inscrito y ACEPTADO en la materia
            $matriculado = Matricula::where('user_id', $alumno->id)
                ->where('materia_id', $sesion->materia_id)
                ->where('estado', 'aceptado')
                ->exists();

            if (!$matriculado) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El alumno ' . $alumno->name . ' NO está aceptado en esta materia.'
                ], 403);
            }

            // Regla 4: Evitar registros duplicados en la misma sesión
            $duplicado = RegistroAsistencia::where('sesion_clase_id', $sesion->id)
                ->where('user_id', $alumno->id)
                ->exists();

            if ($duplicado) {
                return response()->json([
                    'status' => 'warning',
                    'message' => $alumno->name . ' ya tiene registrada su asistencia en esta sesión.'
                ], 409);
            }

            // Regla 5: Calcular si es 'asistencia' o 'retardo' según los minutos de tolerancia
            $horaLimiteAsistencia = Carbon::parse($sesion->fecha . ' ' . $sesion->hora_inicio)
                ->addMinutes($sesion->tolerancia_minutos);

            $ahora = Carbon::now();
            $estadoIncidencia = $ahora->greaterThan($horaLimiteAsistencia) ? 'retardo' : 'asistencia';

            // Guardar en la tabla registro_asistencias
            RegistroAsistencia::create([
                'sesion_clase_id' => $sesion->id,
                'user_id' => $alumno->id,
                'timestamp_escaneo' => $ahora,
                'estado_incidencia' => $estadoIncidencia,
                'observaciones' => 'Registrado por escaneo QR',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => '¡Registrado! ' . $alumno->name . ' (' . strtoupper($estadoIncidencia) . ')',
                'alumno' => $alumno->name,
                'estado' => $estadoIncidencia,
                'hora' => $ahora->format('H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Código QR inválido o corrupto.'
            ], 400);
        }
    }
}