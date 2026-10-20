<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// Sprint 4
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\MatriculaController;
// Sprint 5
use App\Http\Controllers\SesionClaseController;
use App\Http\Controllers\Api\QrController;
// Sprint 6
use App\Http\Controllers\JustificanteController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\HistorialAlumnoController;
use App\Http\Controllers\PerfilAlumnoController;

// Rutas públicas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirección raíz
Route::get('/', function () {
    return redirect('/login');
});

// Panel protegido exclusivamente para PROFESORES
Route::middleware(['auth', \App\Http\Middleware\CheckRol::class . ':profesor'])->group(function () {
    Route::get('/profesor/dashboard', function () {
        return view('profesor.dashboard');
    })->name('profesor.dashboard');

    // Rutas para gestión académica (Sprint 4)
    Route::resource('/profesor/materias', MateriaController::class);
    Route::resource('/profesor/grupos', GrupoController::class);
    Route::get('/profesor/solicitudes', [MatriculaController::class, 'indexSolicitudes'])->name('profesor.solicitudes');
    Route::post('/profesor/solicitudes/manual', [MatriculaController::class, 'inscribirManual'])->name('profesor.matriculas.manual');
    Route::patch('/profesor/solicitudes/{matricula}', [MatriculaController::class, 'actualizarEstado'])->name('profesor.solicitudes.actualizar');
    Route::delete('/profesor/solicitudes/{matricula}', [MatriculaController::class, 'destroy'])->name('profesor.matriculas.destroy');

    // Rutas de Sesiones de Clase y Escaneo QR (Sprint 5)
    Route::get('/profesor/sesiones', [SesionClaseController::class, 'index'])->name('sesiones.index');
    Route::post('/profesor/sesiones/iniciar', [SesionClaseController::class, 'iniciarSesion'])->name('sesiones.iniciar');
    Route::post('/profesor/sesiones/{sesion}/finalizar', [SesionClaseController::class, 'finalizarSesion'])->name('sesiones.finalizar');
    Route::patch('/profesor/asistencias/{registro}', [SesionClaseController::class, 'cambiarEstadoManual'])->name('asistencias.manual');
    Route::post('/profesor/qr/validar', [QrController::class, 'validarEscaneo'])->name('qr.validar');

    // Rutas para gestión de justificantes (Sprint 6)
    Route::get('/profesor/justificantes', [JustificanteController::class, 'indexProfesor'])->name('profesor.justificantes');
    Route::patch('/profesor/justificantes/{id}/estado', [JustificanteController::class, 'actualizarEstado'])->name('profesor.justificantes.estado');

    // Rutas para reportes de asistencias (Sprint 6)
    Route::get('/profesor/reportes', [ReporteController::class, 'index'])->name('profesor.reportes');
    Route::get('/profesor/reportes/csv', [ReporteController::class, 'exportarCsv'])->name('profesor.reportes.csv');

    // Rutas para ver y editar perfil / datos personales del profesor
    Route::get('/profesor/perfil', [PerfilAlumnoController::class, 'editProfesor'])->name('profesor.perfil');
    Route::put('/profesor/perfil', [PerfilAlumnoController::class, 'updateProfesor'])->name('profesor.perfil.update');
});

// Panel protegido exclusivamente para ALUMNOS
Route::middleware(['auth', \App\Http\Middleware\CheckRol::class . ':alumno'])->group(function () {
    Route::get('/alumno/dashboard', function () {
        return view('alumno.dashboard');
    })->name('alumno.dashboard');

    // Rutas de solicitud de grupo (Sprint 4)
    Route::get('/alumno/solicitar-grupo', [MatriculaController::class, 'crearSolicitud'])->name('alumno.solicitar');
    Route::post('/alumno/solicitar-grupo', [MatriculaController::class, 'guardarSolicitud'])->name('alumno.solicitar.guardar');

    // Ruta para generar el QR dinámico en la pantalla del alumno (Sprint 5)
    Route::get('/alumno/qr/generar', [QrController::class, 'generarTokenQR'])->name('qr.generar');

    // Rutas para gestión de justificantes (Sprint 6)
    Route::get('/alumno/justificantes', [JustificanteController::class, 'indexAlumno'])->name('alumno.justificantes');
    Route::post('/alumno/justificantes', [JustificanteController::class, 'store'])->name('alumno.justificantes.store');

    // Rutas para calendario e historial de asistencia (Sprint 6)
    Route::get('/alumno/calendario', [HistorialAlumnoController::class, 'index'])->name('alumno.calendario');

    // Rutas para ver y editar perfil / datos personales del alumno
    Route::get('/alumno/perfil', [PerfilAlumnoController::class, 'edit'])->name('alumno.perfil');
    Route::put('/alumno/perfil', [PerfilAlumnoController::class, 'update'])->name('alumno.perfil.update');
});