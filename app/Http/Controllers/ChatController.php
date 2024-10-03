<?php

namespace App\Http\Controllers;

use App\Events\MensajeEnviado;
use Illuminate\Http\Request;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\ParticipantesConversacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // Método para mostrar los mensajes de una conversación
    public function show($id)
    {
        // Buscar la conversación por ID
        $conversacion = Conversacion::find($id);

        // Verificar si la conversación existe
        if (!$conversacion) {
            return response()->json(['error' => 'Conversación no encontrada'], 404);
        }

        // Verificar si el usuario autenticado es participante de la conversación
        if (!$conversacion->participantes()->where('id_usuario', Auth::id())->exists()) {
            return response()->json(['error' => 'No tienes permiso para ver esta conversación.'], 403);
        }

        // Cargar los mensajes y los participantes
        $conversacion->load('mensajes.usuario', 'participantes.user');

        // Retornar la conversación con los mensajes y participantes
        return response()->json([
            'conversacion' => $conversacion
        ]);
    }



    // Método para enviar un mensaje a una conversación
    public function enviarMensaje(Request $request, $id)
    {
        // Validar que el mensaje esté presente y sea una cadena de texto con un máximo de 255 caracteres
        $request->validate([
            'mensaje' => 'required|string|max:255',
        ]);

        $userId = Auth::id(); // Obtener el ID del usuario autenticado
        $conversacion = Conversacion::find($id); // Buscar la conversación por ID

        if (!$conversacion) {
            return response()->json(['error' => 'Conversación no encontrada'], 404);
        }

        // Verificar que el usuario tenga permiso para enviar mensajes en esta conversación
        if (!$conversacion->usuarios->contains($userId)) {
            return response()->json(['error' => 'No tienes permiso para enviar mensajes en esta conversación.'], 403);
        }

        try {
            // Crear un nuevo mensaje
            $mensaje = Mensaje::create([
                'mensaje' => $request->mensaje,
                'estado' => 1,
                'id_conversacion' => $id,
                'id_usuario' => $userId
            ]);

            // Emitir el evento MensajeEnviado, pasando el mensaje y el usuario autenticado
            event(new MensajeEnviado($mensaje, Auth::user()));

            // Log para verificar que el evento se emite
            Log::info('Evento MensajeEnviado emitido', ['mensaje' => $mensaje, 'conversacion' => $conversacion]);

            // Retornar la respuesta JSON con el mensaje creado
            return response()->json(['mensaje' => $mensaje]);
        } catch (\Exception $e) {
            Log::error('Error al enviar el mensaje: ' . $e->getMessage());
            return response()->json(['error' => 'Error al enviar el mensaje', 'details' => $e->getMessage()], 500);
        }
    }


    // ChatController.php
    public function usuariosPorCursos()
    {
        $usuario = Auth::user();

        // Obtener los cursos en los que el usuario está inscrito
        $cursos = $usuario->cursosEstudiante;

        // Obtener los IDs de los cursos
        $cursosIds = $cursos->pluck('id');

        // Obtener los usuarios inscritos en estos cursos, excluyendo al usuario autenticado
        $usuarios = User::whereIn('id', function ($query) use ($cursosIds) {
            $query->select('id_estudiante') // Columna en la tabla pivot que referencia al usuario
                ->from('curso_estudiante') // Nombre de la tabla pivot
                ->whereIn('id_curso', $cursosIds); // Columna que referencia al curso
        })->where('id', '<>', $usuario->id) // Excluir al usuario autenticado
            ->get();

        return response()->json([
            'usuarios' => $usuarios
        ]);
    }

    // Método para verificar o crear una conversación
    public function obtenerOCrearConversacion($id)
    {
        // ID del usuario autenticado
        $idUsuarioAutenticado = Auth::id();

        // Verificar si existe una conversación entre el usuario autenticado y el otro usuario
        $conversacion = Conversacion::whereHas('participantes', function ($query) use ($idUsuarioAutenticado) {
            $query->where('id_usuario', $idUsuarioAutenticado);
        })
            ->whereHas('participantes', function ($query) use ($id) {
                $query->where('id_usuario', $id);
            })
            ->first();

        // Si no existe una conversación, crearla
        if (!$conversacion) {
            $conversacion = new Conversacion();
            $conversacion->estado = 1;
            $conversacion->id_tipo_conversacion = 1;
            $conversacion->save();

            // Añadir al usuario autenticado como participante
            ParticipantesConversacion::create([
                'id_conversacion' => $conversacion->id,
                'id_usuario' => $idUsuarioAutenticado,
            ]);

            // Añadir al otro usuario como participante
            ParticipantesConversacion::create([
                'id_conversacion' => $conversacion->id,
                'id_usuario' => $id,
            ]);
        }

        // Cargar los mensajes y los participantes de la conversación
        $conversacion->load('mensajes.usuario', 'participantes.user');

        // Retornar la conversación en formato JSON
        return response()->json([
            'conversacion' => $conversacion
        ]);
    }
}
