<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArchivoLeccionRequest;
use App\Models\ArchivoLeccion;
use App\Models\Leccion;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArchivoLeccionController extends Controller
{

    /**
     * Obtener todos los archivos
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Obtener todos los archivos de la leccion
            $archivos = ArchivoLeccion::all();

            return response()->json([
                'archivos' => $archivos,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los cursos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar todos los archivos de una lección.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            // Buscar los archivos relacionados a la lección con el ID especificado
            $archivosLeccion = ArchivoLeccion::where('id_leccion', $id)->get();

            return response()->json([
                'archivosLeccion' => $archivosLeccion
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'La lección no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los archivos de la lección: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Almacenar un nuevo archivo de lección.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(CreateArchivoLeccionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($data["tipo"] === "IMG") {
                // Manejar la carga de la imagen
                if ($request->hasFile('ubicacion')) {
                    $path = $request->file('ubicacion')->store('imagenes', 'public');
                    $data['ubicacion'] = $path;
                }
            } else if ($data["tipo"] === "PDF") {
                // Manejar la carga de la pdf
                if ($request->hasFile('ubicacion')) {
                    $path = $request->file('ubicacion')->store('pdf', 'public');
                    $data['ubicacion'] = $path;
                }
            } else if ($data["tipo"] === "VIDEO") {
                // Manejar la carga de la video
                if ($request->hasFile('ubicacion')) {
                    $path = $request->file('ubicacion')->store('video', 'public');
                    $data['ubicacion'] = $path;
                }
            }

            $archivoLeccion = ArchivoLeccion::create($data);

            // Obtener la lección a la que pertenece el archivo
            $leccion = Leccion::findOrFail($data['id_leccion'])->load('archivos');

            return response()->json([
                'message' => 'Archivo agregado correctamente a la lección.',
                "leccion" => $leccion
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al agregar el archivo a la lección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un archivo de lección existente.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $archivoLeccion = ArchivoLeccion::findOrFail($id);
            $archivoLeccion->delete();
            $leccion = Leccion::findOrFail($archivoLeccion->id_leccion)->load('archivos');

            return response()->json([
                'message' => 'Archivo eliminado correctamente de la lección.',
                'leccion' => $leccion,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el archivo de la lección: ' . $e->getMessage()
            ], 500);
        }
    }
}
