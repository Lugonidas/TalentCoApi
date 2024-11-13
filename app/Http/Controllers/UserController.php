<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\RegistroUsuarioMailable;
use App\Models\User;
use App\Models\Rol;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Obtener todos los usuarios.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Almacenar en caché los usuarios por 60 minutos
            $usuarios = Cache::remember('usuarios', 60, function () {
                return User::with('cursosEstudiante', 'rol', 'conversaciones')->get();
            });

            return response()->json([
                'usuarios' => $usuarios,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los usuarios: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Mostrar un usuario por su ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            // Usar el ID del usuario para crear una clave única
            $usuario = Cache::remember("usuario_{$id}", 60, function () use ($id) {
                return User::with('cursosEstudiante')->findOrFail($id);
            });

            return response()->json([
                'usuario' => $usuario
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el usuario: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Registrar un nuevo usuario.
     *
     * @param  CreateUserRequest  $request
     * @return JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            /* // Verificar si se subió una imagen
            if ($request->hasFile('imagen')) {
                // Almacenar la imagen en la carpeta 'imagenes' del almacenamiento público
                $path = $request->file('imagen')->store('imagenes', 'public');
                $data['imagen'] = $path;
            } else {
                // Asignar una imagen por defecto si no se subió ninguna
                $data['imagen'] = 'imagenes/avatar.png'; // Ruta de la imagen por defecto
            } */

            // Crear un nuevo usuario con los datos proporcionados
            $usuario = User::create([
                'name' => $data['nombre'],
                'apellido' => $data['apellido'],
                'numero_documento' => $data['numero_documento'],
                'usuario' => $data['usuario'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'direccion' => $data['direccion'],
                'id_tipo_documento' => $data['id_tipo_documento'],
                'id_rol' => $data['id_rol'],
                'imagen' => "https://w7.pngwing.com/pngs/178/595/png-transparent-user-profile-computer-icons-login-user-avatars-thumbnail.png", // Imagen ya sea subida o por defecto
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Construir la URL de verificación usando la URL del frontend
            $frontendUrl = config('app.frontend_url');
            $verificationUrl = "{$frontendUrl}/verify/{$usuario->id}/" . sha1($usuario->email);

            // Enviar el correo de bienvenida con la URL del frontend
            Mail::to($usuario->email)->send(new RegistroUsuarioMailable($usuario, $verificationUrl));


            return response()->json([
                'message' => 'Usuario registrado correctamente. Por favor, verifica tu correo.',
                'token' => $usuario->createToken("token")->plainTextToken,
                'usuario' => $usuario
            ], 201);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();

            return response()->json([
                'message' => 'Error al registrar el usuario: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al registrar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Actualizar un usuario existente.
     *
     * @param  UpdateUserRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        try {
            $usuario = User::with('cursosEstudiante')->findOrFail($id);
            $data = $request->validated();

            $usuario->update([
                'name' => $data['nombre'],
                'apellido' => $data['apellido'],
                'numero_documento' => $data['numero_documento'],
                'usuario' => $data['usuario'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'direccion' => $data['direccion'],
                'email' => $data['email'],
                'imagen' => $data['imagen'],
                'id_tipo_documento' => $data['id_tipo_documento']
            ]);

            // Eliminar la caché del usuario actualizado
            Cache::forget("usuario_{$id}");

            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'usuario' => $usuario
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un usuario existente.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $usuario = User::findOrFail($id);
            $usuario->delete();

            // Eliminar la caché del usuario eliminado
            Cache::forget("usuario_{$id}");

            return response()->json([
                'message' => 'Usuario eliminado correctamente',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar usuarios por nombre, apellido, usuario o email.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function buscarUsuarios(Request $request): JsonResponse
    {
        try {
            $query = $request->input('busqueda');
            $cacheKey = "usuarios_busqueda_{$query}";

            // Usar caché para almacenar los resultados de búsqueda
            $usuarios = Cache::remember($cacheKey, 60, function () use ($query) {
                return User::with('cursosEstudiante', 'rol')
                    ->where('name', 'like', "%$query%")
                    ->orWhere('apellido', 'like', "%$query%")
                    ->orWhere('usuario', 'like', "%$query%")
                    ->orWhere('email', 'like', "%$query%")
                    ->get();
            });

            return response()->json([
                'usuarios' => $usuarios,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al buscar usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un usuario existente.
     *
     * @param  UpdateUserRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request, $id): JsonResponse
    {
        try {
            // Buscar el usuario por su ID con sus cursos relacionados
            $usuario = User::with('cursosEstudiante')->findOrFail($id);

            $data = $request->validated();

            // Actualizar el usuario con los datos proporcionados
            $usuario->update([
                'name' => $data['nombre'],
                'apellido' => $data['apellido'],
                'usuario' => $data['usuario'],
                'direccion' => $data['direccion'],
                'email' => $data['email'],
                'imagen' => $data['imagen'],
            ]);

            // Recargar el usuario con sus cursos actualizados
            $usuario->refresh();

            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'usuario' => $usuario
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();

            return response()->json([
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage(),
                'errors' => $errors
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request, $id): JsonResponse
    {
        Log::info("Desde actualizar contraseña",  [$request->all()]);
        try {
            $usuario = User::findOrFail($id);

            // Validar la solicitud
            $request->validated();

            // Verificar si la nueva contraseña es diferente a la actual
            if (Hash::check($request->password, $usuario->password)) {
                return response()->json(['message' => 'La nueva contraseña no puede ser igual a la contraseña actual.'], 422);
            }

            // Verificar la contraseña actual
            if (!Hash::check($request->password_actual, $usuario->password)) {
                return response()->json(['message' => 'La contraseña actual no es válida.'], 422);
            }

            // Actualizar la contraseña
            $usuario->update(['password' => Hash::make($request->password)]);

            return response()->json(['message' => 'Contraseña actualizada correctamente.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $errors,
            ], 422);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar la contraseña: ' . $e->getMessage()], 500);
        }
    }
}
