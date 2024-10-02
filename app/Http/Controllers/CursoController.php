<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Http\Requests\CreateCursoRequest;
use App\Http\Requests\UpdateCursoRequest;
use App\Models\Inscripcion;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CursoController extends Controller
{
    /**
     * Obtener todos los cursos.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $cursos = Cache::remember('cursos_all', 60, function () {
                return Curso::with([
                    'comentarios',
                    'comentarios.user',
                    'docente',
                    'lecciones' => function ($query) {
                        $query->orderBy('orden', 'desc');
                    },
                    'categoria',
                    'estudiantes'
                ])->orderBy('created_at', 'desc')->get();
            });

            return response()->json(['cursos' => $cursos]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los cursos: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Mostrar un curso por su ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */

    public function show($id): JsonResponse
    {
        try {
            $curso = Curso::with('docente', 'lecciones.archivos', 'categoria', 'comentarios', 'comentarios.user', 'estudiantes')
                ->findOrFail($id);

            return response()->json(['curso' => $curso]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El curso no existe'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener el curso: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Registrar un nuevo curso.
     *
     * @param  CreateCursoRequest  $request
     * @return JsonResponse
     */
    // app/Http/Controllers/CursoController.php

    public function store(CreateCursoRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Manejar la carga de la imagen
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('imagenes', 'public');
                $data['imagen'] = $path;
            }

            // Crear un nuevo curso con los datos proporcionados
            $curso = Curso::create([
                'titulo' => $data['titulo'],
                'imagen' => $data['imagen'],
                'duracion' => $data['duracion'],
                'estado' => $data['estado'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'descripcion' => $data['descripcion'],
                'id_docente' => $data['id_docente'],
                'id_categoria' => $data['id_categoria'],
            ]);

            Cache::forget('cursos_all');

            return response()->json([
                'message' => 'Curso agregado correctamente',
                'curso' => $curso
            ], 201);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();

            return response()->json([
                'message' => 'Error al agregar el curso: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al agregar el curso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un curso existente.
     *
     * @param  UpdateCursoRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update($id, UpdateCursoRequest $request): JsonResponse
    {
        try {
            // Buscar el curso
            $curso = Curso::findOrFail($id);

            // Obtener los datos validados
            $data = $request->validated();

            // Manejar la imagen si se proporciona
            if ($request->hasFile('imagen')) {
                // Validar la imagen (si no está validada en UpdateCursoRequest)
                $request->validate([
                    'imagen' => ["nullable", "image", "mimes:jpeg,png,jpg,gif,svg", "max:4096"],
                ]);

                // Eliminar la imagen anterior si existe
                if ($curso->imagen && Storage::disk('public')->exists($curso->imagen)) {
                    Storage::disk('public')->delete($curso->imagen);
                }

                // Guardar la nueva imagen
                $path = $request->file('imagen')->store('imagenes', 'public');
                $data['imagen'] = $path;
            }

            // Actualizar el curso con los datos nuevos
            $curso->update($data);

            // Invalidar la caché después de actualizar el curso
            Cache::forget('cursos_all');

            return response()->json([
                'message' => 'Curso actualizado correctamente',
                'curso' => $curso
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Curso no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar el curso', 'details' => $e->getMessage()], 500);
        }
    }


    /**
     * Eliminar un curso existente.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Buscar el curso por su ID
            $curso = Curso::findOrFail($id);

            // Eliminar la relación en curso_estudiante
            Inscripcion::where('id_curso', $id)->delete();

            // Eliminar el curso
            $curso->delete();

            return response()->json([
                'message' => 'Curso eliminado correctamente',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El curso no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el curso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cursosMasPopulares(): JsonResponse
    {
        try {
            $cursosPopulares = Cache::remember('cursos_populares', 60, function () {
                return Curso::with('docente', 'lecciones', 'categoria', 'comentarios')
                    ->withCount('estudiantes')
                    ->orderByDesc('estudiantes_count')
                    ->take(8)
                    ->get();
            });

            return response()->json(['cursosPopulares' => $cursosPopulares]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los cursos más populares: ' . $e->getMessage()], 500);
        }
    }


    public function cursosDelDocente($idDocente): JsonResponse
    {
        try {
            $cursos = Cache::remember("cursos_docente_{$idDocente}", 60, function () use ($idDocente) {
                return Curso::where('id_docente', $idDocente)
                    ->with("docente", "lecciones", "categoria", "comentarios", "estudiantes")
                    ->orderBy('created_at', 'desc')
                    ->get();
            });

            return response()->json(['cursos' => $cursos]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El docente no existe'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los cursos del docente: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todos los cursos en los que está inscrito un estudiante específico.
     *
     * @param  int  $idEstudiante
     * @return JsonResponse
     */
    public function cursosDelEstudiante($idEstudiante): JsonResponse
    {
        try {
            $cursos = Cache::remember("cursos_estudiante_{$idEstudiante}", 60, function () use ($idEstudiante) {
                $estudiante = User::findOrFail($idEstudiante);
                return $estudiante->cursosEstudiante()
                    ->with('docente', 'lecciones', 'categoria', 'comentarios', 'estudiantes')
                    ->get();
            });

            return response()->json(['cursos' => $cursos]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El estudiante no existe'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los cursos del estudiante: ' . $e->getMessage()], 500);
        }
    }


    public function mostrarCursos(): JsonResponse
    {
        try {
            $cursos = Cache::remember('cursos_all_detailed', 60, function () {
                return Curso::with("docente", "lecciones", "categoria", "comentarios", "comentarios.user", "estudiantes")
                    ->orderBy('created_at', 'desc')
                    ->get();
            });

            return response()->json(['cursos' => $cursos]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener los cursos: ' . $e->getMessage()], 500);
        }
    }

    public function descargarPDF($id)
    {
        try {
            $curso = Curso::with('estudiantes', 'docente')
                ->findOrFail($id);

            // Generar el PDF
            $pdf = PDF::loadView('pdf.curso', compact('curso', ));

            // Retornar el PDF como una descarga
            return $pdf->download("curso_{$curso->titulo}.pdf");
        } catch (Exception $e) {
            // Manejo de errores: podrías registrar el error o lanzar una excepción
            return response()->json(['message' => 'Error al generar el PDF: ' . $e->getMessage()], 500);
        }
    }
}
