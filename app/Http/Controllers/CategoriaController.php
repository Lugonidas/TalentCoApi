<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class CategoriaController extends Controller
{
    /**
     * Obtener todas las categorías.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $categorias = Cache::remember('categorias_all', 60, function () {
                return Categoria::with('cursos')->get();
            });

            return response()->json([
                'categorias' => $categorias
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las categorias: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mostrarCategorias(): JsonResponse
    {
        try {
            $categorias = Cache::remember('categorias_all', 60, function () {
                return Categoria::with('cursos')->get();
            });

            return response()->json([
                'categorias' => $categorias
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las categorias: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Almacenar una nueva categoría.
     *
     * @param  CreateCategoriaRequest  $request
     * @return JsonResponse
     */
    public function store(CreateCategoriaRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $categoria = Categoria::create($data);

            // Limpiar caché si es necesario
            Cache::forget('categorias_all');

            return response()->json([
                'message' => 'Categoría creada correctamente',
                'categoria' => $categoria,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al crear la categoría: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Mostrar una categoría por su ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $categoria = Cache::remember("categoria_{$id}", 60, function () use ($id) {
                return Categoria::findOrFail($id);
            });

            return response()->json([
                'categoria' => $categoria
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La categoría no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la categoría: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Actualizar una categoría existente.
     *
     * @param  UpdateCategoriaRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateCategoriaRequest $request, $id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);

            $data = $request->validated();

            $categoria->update($data);

            // Limpiar caché si es necesario
            Cache::forget("categoria_{$id}");
            Cache::forget('categorias_all');

            return response()->json([
                'message' => 'Categoría actualizada correctamente',
                'categoria' => $categoria
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La categoría no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la categoría: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Eliminar una categoría existente.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();

            // Limpiar caché si es necesario
            Cache::forget("categoria_{$id}");
            Cache::forget('categorias_all');

            return response()->json([
                'message' => 'Categoría eliminada correctamente',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La categoría no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la categoría: ' . $e->getMessage()
            ], 500);
        }
    }
}
