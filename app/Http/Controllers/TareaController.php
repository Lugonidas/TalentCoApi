<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTareaRequest;
use App\Http\Requests\UpdateTareaRequest;
use App\Models\Curso;
use App\Models\Tarea;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PDF;

class TareaController extends Controller
{

    public function index(): JsonResponse
    {
        try {
            // Obtener todas las tareaes
            $tareas = Tarea::all();

            // Devolver la respuesta JSON con las tareaes
            return response()->json([
                'tareas' => $tareas
            ]);
        } catch (Exception $e) {
            // En caso de error, devolver una respuesta JSON con un mensaje de error
            return response()->json([
                'message' => 'Error al obtener las tareas: ' . $e->getMessage()
            ], 500); // Código de estado HTTP 500 para indicar un error del servidor
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $tarea = Tarea::with(['respuestas.estudiante', 'respuestas.nota' => function ($query) use ($id) {
                $query->where('id_evaluacion', $id); // Aquí filtras por la evaluación correspondiente
            }])->findOrFail($id);

            return response()->json([
                'tarea' => $tarea
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La tarea no existe'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener tarea: ' . $e->getMessage()], 500);
        }
    }



    public function store(CreateTareaRequest $request): JsonResponse
    {
        Log::info('Datos recibidos Crear Tarea:', $request->all());
        try {
            $data = $request->validated();

            // Manejar el archivo cargado
            if ($request->hasFile('archivo')) {
                $path = $request->file('archivo')->store('archivos', 'public');
                $data['archivo'] = $path;
            }

            // Crear una nueva tarea
            $tarea = Tarea::create($data);

            return response()->json([
                'message' => 'Tarea registrada correctamente',
                'tarea' => $tarea
            ], 201); // Código de estado HTTP 201 para indicar éxito en la creación
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            // En caso de error, devolver una respuesta JSON con un mensaje de error
            return response()->json([
                'message' => 'Error al registrar la tarea: ' . $e->getMessage(),
                'errors' => $errors
            ], 422); // Código de estado HTTP 422 para indicar una solicitud mal formada debido a errores de validación
        } catch (Exception $e) {
            // En caso de otros errores, devuelve un mensaje genérico de error
            return response()->json([
                'message' => 'Error al registrar la tarea: ' . $e->getMessage()
            ], 500); // Código de estado HTTP 500 para indicar un error del servidor
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTareaRequest $request, $id): JsonResponse
    {
        Log::info('Datos recibidos Actualizar:', $request->all());

        try {
            $tarea = Tarea::findOrFail($id);

            $data = $request->validated();

            if ($request->hasFile('archivo')) {
                // Eliminar el archivo anterior si existe
                if ($tarea->archivo) {
                    Storage::disk('public')->delete($tarea->archivo);
                }

                // Guardar el nuevo archivo
                $archivoPath = $request->file('archivo')->store('archivos', 'public');
                $data['archivo'] = $archivoPath;
            } else {
                // Mantener el archivo anterior si no se ha subido uno nuevo
                $data['archivo'] = $tarea->archivo;
            }

            $tarea->update($data);

            $tareas =  Tarea::findOrFail($tarea->id);

            return response()->json([
                'message' => 'Tarea actualizada correctamente',
                'tarea' => $tarea,
                'tareas' => $tareas
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La tarea no existe'], 404);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'message' => 'Error al actualizar la tarea: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Encuentra el usuario por su ID
            $tareas = Tarea::findOrFail($id);
            // Verificar si el usuario existe
            if (!$tareas) {
                return response()->json([
                    'message' => 'la tarea no existe'
                ], 404); // Código de estado HTTP 404 para indicar que el recurso no se encontró
            }

            // Si el usuario existe, intentar eliminarlo
            $tareas->delete();

            return response()->json([
                'message' => 'tarea eliminada correctamente',
            ]);
        } catch (Exception $e) {
            // En caso de error, devolver una respuesta JSON con un mensaje de error
            return response()->json([
                'message' => 'Error al eliminar la tarea: ' . $e->getMessage()
            ], 500); // Código de estado HTTP 500 para indicar un error del servidor
        }
    }

    public function getTareasByCurso($cursoId): JsonResponse
    {
        try {
            // Verificar si el curso existe
            $curso = Curso::findOrFail($cursoId);

            if (!$curso) {
                return response()->json(['message' => 'Curso no encontrado'], 404);
            }

            // Obtener todas las tareas del curso con las respuestas de los estudiantes
            $tareas = Tarea::where('id_curso', $cursoId)
                ->with(['respuestas.estudiante', 'respuestas.nota'])
                ->get();

            // Verificar si existen tareas para ese curso
            if ($tareas->isEmpty()) {
                return response()->json(['message' => 'No se encontraron tareas para este curso'], 404);
            }

            // Devolver las tareas y respuestas en formato JSON
            return response()->json(['tareas' => $tareas], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener las tareas: ' . $e->getMessage()], 500);
        }
    }

    public function descargarPDF($id)
    {
        try {
            $tarea = Tarea::where('id', $id)
                ->with(['respuestas.estudiante', 'respuestas.nota'])
                ->first();  // Cambiado a first() para obtener una sola tarea

            // Verifica si la tarea existe
            if (!$tarea) {
                return response()->json(['message' => 'Tarea no encontrada'], 404);
            }

            Log::info($tarea);

            // Generar el PDF
            $pdf = PDF::loadView('pdf.tarea', compact('tarea'));

            // Retornar el PDF como una descarga
            return $pdf->download("tarea_{$tarea->titulo}.pdf");
        } catch (Exception $e) {
            // Manejo de errores
            return response()->json(['message' => 'Error al generar el PDF: ' . $e->getMessage()], 500);
        }
    }
}
