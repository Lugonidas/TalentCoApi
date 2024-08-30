<?php

namespace App\Http\Controllers;

use App\Models\ArchivoLeccion;
use App\Models\ArchivoVisto;
use App\Models\Leccion;
use Illuminate\Http\Request;

class ArchivoVistoController extends Controller
{
    // Registrar que un archivo ha sido visto por un usuario
    public function registrarVisto($idArchivo)
    {
        $usuarioId = auth()->user()->id; // Obtén el ID del usuario autenticado

        // Registrar que el archivo ha sido visto
        ArchivoVisto::firstOrCreate([
            'id_usuario' => $usuarioId,
            'id_archivo' => $idArchivo
        ]);

        return response()->json(['message' => 'Archivo registrado como visto.'], 200);
    }

    // Obtener lista de archivos vistos por el usuario
    public function archivosVistos()
    {
        $usuarioId = auth()->user()->id;

        $archivosVistos = ArchivoVisto::where('id_usuario', $usuarioId)->get();

        return response()->json($archivosVistos);
    }

    // Método para obtener el progreso de una lección
    public function progresoLeccion($idLeccion)
    {
        $usuarioId = auth()->user()->id;

        // Obtener todos los archivos de la lección
        $archivos = ArchivoLeccion::where('id_leccion', $idLeccion)->get();
        $totalArchivos = $archivos->count();

        if ($totalArchivos === 0) {
            return response()->json(['progreso' => 0], 200);
        }

        // Obtener todos los archivos vistos por el usuario en esta lección
        $archivosVistos = ArchivoVisto::where('id_usuario', $usuarioId)
            ->whereIn('id_archivo', $archivos->pluck('id'))
            ->count();

        $progreso = ($archivosVistos / $totalArchivos) * 100;

        return response()->json(['progreso' => $progreso], 200);
    }

    // Método para obtener el progreso de un curso
    public function progresoCurso($idCurso)
    {
        $usuarioId = auth()->user()->id;

        // Obtener todas las lecciones del curso
        $lecciones = Leccion::where('id_curso', $idCurso)->get();
        $totalLecciones = $lecciones->count();

        if ($totalLecciones === 0) {
            return response()->json(['progreso' => 0], 200);
        }

        // Contar cuántas lecciones tienen al menos un archivo visto
        $leccionesConArchivosVistos = 0;

        foreach ($lecciones as $leccion) {
            $archivos = ArchivoLeccion::where('id_leccion', $leccion->id)->get();
            $totalArchivos = $archivos->count();

            if ($totalArchivos === 0) {
                continue;
            }

            $archivosVistos = ArchivoVisto::where('id_usuario', $usuarioId)
                ->whereIn('id_archivo', $archivos->pluck('id'))
                ->count();

            if ($archivosVistos > 0) {
                $leccionesConArchivosVistos++;
            }
        }

        $progreso = ($leccionesConArchivosVistos / $totalLecciones) * 100;

        return response()->json(['progreso' => $progreso], 200);
    }

    public function hasViewedArchivo($userId, $archivoId)
    {
        $visto = ArchivoVisto::where('id_usuario', $userId)
            ->where('id_archivo', $archivoId)
            ->exists();

        return response()->json([
            'hasViewed' => $visto
        ]);
    }
}
