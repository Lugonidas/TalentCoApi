<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRespuestaRequest;
use App\Models\RespuestaEstudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RespuestaEstudianteController extends Controller
{
    /**
     * Muestra las respuestas de los estudiantes para una evaluación específica.
     *
     * @param int $tareaId
     * @return JsonResponse
     */
    /*     public function index($tareaId)
    {
        // Obtener la evaluación con las respuestas y los estudiantes que respondieron
        $tarea = Tarea::with('respuestas.estudiante')->findOrFail($tareaId);

        return response()->json([
            'tarea' => $tarea,
            'message' => 'Respuestas obtenidas con éxito'
        ], 200);
    } */

    public function store(CreateRespuestaRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();


            if ($request->hasFile('archivo')) {
                // Solo guarda la ruta del archivo
                $archivoPath = $request->file('archivo')->store('respuestas', 'public');
                $data['archivo'] = $archivoPath;  // Asegúrate de que esta línea esté guardando la ruta
            }

            // Crear la nueva respuesta
            $respuesta = RespuestaEstudiante::create(
                [
                    "id_evaluacion" => $data["id_evaluacion"],
                    "id_estudiante" => $data["id_estudiante"],
                    "archivo" => $data["archivo"],
                    "texto_respuesta" => $data["texto_respuesta"],
                    "fecha_entrega" => now()
                ]
            );

            return response()->json([
                'respuesta' => $respuesta,
                'message' => 'Respuesta guardada con éxito'
            ], 201);
        } catch (\Exception $e) {
            Log::info('Error al guardar la respuesta DATA: ' . $data);
            Log::info('Error al guardar la respuesta: ' . $e->getMessage());
            // Capturar cualquier excepción y devolver un error JSON
            return response()->json([
                'error' => 'Error al guardar la respuesta',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza una respuesta existente.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'archivo' => 'nullable|file|mimes:pdf,docx,png,jpg|max:2048',
            'texto_respuesta' => 'nullable|string',
        ]);

        // Obtener la respuesta que se va a actualizar
        $respuesta = RespuestaEstudiante::findOrFail($id);

        // Subir el archivo si existe
        if ($request->hasFile('archivo')) {
            // Elimina el archivo anterior si existe
            if ($respuesta->archivo) {
                Storage::delete($respuesta->archivo);
            }

            // Subir el nuevo archivo
            $archivoPath = $request->file('archivo')->store('respuestas');
        } else {
            $archivoPath = $respuesta->archivo; // Mantener el archivo existente
        }

        // Actualizar la respuesta
        $respuesta->update([
            'archivo' => $archivoPath,
            'texto_respuesta' => $request->input('texto_respuesta'),
        ]);

        return response()->json([
            'respuesta' => $respuesta,
            'message' => 'Respuesta actualizada con éxito'
        ], 200);
    }

    /**
     * Elimina una respuesta del estudiante.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        // Obtener la respuesta
        $respuesta = RespuestaEstudiante::findOrFail($id);

        // Eliminar el archivo si existe
        if ($respuesta->archivo) {
            Storage::delete($respuesta->archivo);
        }

        // Eliminar la respuesta
        $respuesta->delete();

        return response()->json([
            'message' => 'Respuesta eliminada con éxito'
        ], 200);
    }
}
