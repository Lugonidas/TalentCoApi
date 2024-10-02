<?php

use App\Http\Controllers\RespuestaEstudianteController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ArchivoLeccionController;
use App\Http\Controllers\ArchivoVistoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\LeccionController;
use App\Http\Controllers\NotasEstudiantesController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas de la API para tu aplicación.
| Estas rutas son cargadas por RouteServiceProvider y todas serán asignadas al
| grupo de middleware "api". ¡Haz algo genial!
|
*/


// routes/web.php
/* Route::get('/test-log', function () {
    Log::info('Esto es un mensaje de prueba de logging');
    return 'Revisa el archivo de log';
});
 */

Route::get('/verify/{id}/{hash}', [VerificationController::class, 'verify']);


Route::options('/archivo-leccion', function () {
    return response()->json([], 200);
});

// Rutas para recursos protegidos por autenticación
Route::middleware('auth:sanctum', 'verified')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });


    Route::apiResource('/notas', NotasEstudiantesController::class);
    Route::put('/notas/actualizar', [NotasEstudiantesController::class, 'update']);

    Route::apiResource('/tareas', TareaController::class);
    Route::get('tareas/curso/{cursoId}', [TareaController::class, 'getTareasByCurso']);
    Route::apiResource('/respuestas', RespuestaEstudianteController::class);
    Route::get('/tarea/{id}/descargar-pdf', [TareaController::class, 'descargarPDF']);

    // Rutas relacionadas con archivos y progreso de cursos
    Route::get('/archivos/{userId}/{archivoId}/visto', [ArchivoVistoController::class, 'hasViewedArchivo']);
    Route::post('/archivos/{id}/visto', [ArchivoVistoController::class, 'registrarVisto']);
    Route::get('/archivos/vistos', [ArchivoVistoController::class, 'archivosVistos']);
    Route::get('/lecciones/{id}/progreso', [ArchivoVistoController::class, 'progresoLeccion']);
    Route::get('/cursos/{id}/progreso', [ArchivoVistoController::class, 'progresoCurso']);

    // Rutas de autenticación
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de usuarios
    Route::apiResource('/usuarios', UserController::class);
    Route::put('/usuarios/perfil/{id}', [UserController::class, "updateProfile"]);
    Route::put('/usuarios/{id}/update-password', [UserController::class, 'updatePassword']);
    Route::post('/usuarios/buscar', [UserController::class, "buscarUsuarios"]);

    // Rutas de cursos y lecciones
    Route::get('/curso/{id}/descargar-pdf', [CursoController::class, 'descargarPDF']);

    Route::apiResource('/cursos', CursoController::class)->except(['index', "show"]);
    Route::get('/cursos-populares', [CursoController::class, "cursosMasPopulares"]);
    Route::get('/cursos/docente/{idDocente}', [CursoController::class, 'cursosDelDocente']);
    Route::get('/cursos/estudiante/{idEstudiante}', [CursoController::class, 'cursosDelEstudiante']);
    Route::apiResource('/lecciones', LeccionController::class);
    Route::apiResource('/archivo-leccion', ArchivoLeccionController::class);
    Route::apiResource('/categorias', CategoriaController::class)->except(['index']);
    Route::apiResource('/comentarios', ComentarioController::class);
    Route::apiResource('/inscripcion', InscripcionController::class);

    // Rutas del chat
    Route::get('/chat/usuarios-cursos', [ChatController::class, 'usuariosPorCursos']);
    Route::post('/chat/conversaciones/crear/{id}', [ChatController::class, 'obtenerOCrearConversacion']);
    Route::get('/chat/{id}', [ChatController::class, 'show']);
    Route::post('/chat/{id}/enviar-mensaje', [ChatController::class, 'enviarMensaje']);
});

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registrarse', [AuthController::class, 'store']);
Route::get('/categorias', [CategoriaController::class, "index"]);
Route::get('/cursos', [CursoController::class, 'index']);
Route::get('/cursos/{curso}', [CursoController::class, 'show']);
Route::get('/lecciones/curso/{curso}', [LeccionController::class, 'showAllLessons']);

/* Route::get('/phpinfo', function() {
    phpinfo();
}); */
/* Pruebas */