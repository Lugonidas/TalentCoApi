<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLeccionRequest;
use App\Http\Requests\UpdateLeccionRequest;
use App\Models\Curso;
use App\Models\Leccion;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LeccionController extends Controller
{
    /**
     * Mostrar todas las lecciones.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $lecciones = Leccion::with("curso", "archivos")->orderBy('created_at', 'desc')->get();

            return response()->json([
                'lecciones' => $lecciones,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las lecciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una lección específica.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $leccion = Leccion::with('curso', 'archivos')->findOrFail($id);

            return response()->json([
                'leccion' => $leccion
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La lección no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la lección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar todas las lecciones de un curso específico.
     *
     * @param  int  $cursoId
     * @return JsonResponse
     */
    public function showAllLessons($cursoId): JsonResponse
    {
        try {
            $curso = Curso::with('lecciones.archivos')->findOrFail($cursoId);

            return response()->json([
                'curso' => $curso,
                'lecciones' => $curso->lecciones
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El curso no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las lecciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacenar una nueva lección.
     *
     * @param  CreateLeccionRequest  $request
     * @return JsonResponse
     */
    public function store(CreateLeccionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Manejar la carga de la imagen
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('imagenes', 'public');
                $data['imagen'] = $path;
            }

            $leccion = Leccion::create($data);

            return response()->json([
                'message' => 'Lección agregada correctamente',
                'leccion' => $leccion
            ], 201);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'message' => 'Error al agregar la lección: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al agregar la lección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una lección existente.
     *
     * @param  UpdateLeccionRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateLeccionRequest $request, $id): JsonResponse
    {
        try {
            $leccion = Leccion::findOrFail($id);

            // Validar los datos del request
            $data = $request->validated();

            // Manejo de la imagen
            if ($request->hasFile('imagen')) {
                // Eliminar la imagen anterior si existe
                if ($leccion->imagen && Storage::disk('public')->exists($leccion->imagen)) {
                    Storage::disk('public')->delete($leccion->imagen);
                }

                // Guardar la nueva imagen
                $path = $request->file('imagen')->store('imagenes', 'public');
                $data['imagen'] = $path;
            }

            // Actualizar los datos de la lección
            $leccion->update($data);

            return response()->json([
                'message' => 'Lección actualizada correctamente',
                'leccion' => $leccion
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La lección no existe'], 404);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'message' => 'Error al actualizar la lección: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la lección'], 500);
        }
    }


    /**
     * Eliminar una lección existente.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $leccion = Leccion::findOrFail($id);
            $leccion->delete();

            return response()->json([
                'message' => 'Lección eliminada correctamente',
                'leccion' => $leccion,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la lección: ' . $e->getMessage()
            ], 500);
        }
    }
}
