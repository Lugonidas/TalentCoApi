<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Mail\RegistroUsuarioMailable;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        // Validar la solicitud de inicio de sesión
        $credenciales = $request->validated();

        // Intentar autenticar al usuario
        if (!Auth::attempt($credenciales)) {
            // Verificar si el usuario existe
            $usuario = User::where('email', $credenciales['email'])->first();
            if (!$usuario) {
                return response()->json([
                    'errors' => ['Usuario no existe.']
                ], 422);
            }

            // Si el usuario existe pero las credenciales son incorrectas
            return response()->json([
                'errors' => ['Credenciales incorrectas.']
            ], 422);
        }

        // Obtener el usuario autenticado
        $usuario = Auth::user();

        // Verificar si el correo electrónico del usuario está verificado
        if (!$usuario->hasVerifiedEmail()) {
            return response()->json([
                'errors' => ['Debes verificar tu correo electrónico antes de iniciar sesión.']
            ], 403); // Forbidden
        }

        // Cargar información adicional del usuario
        $usuario->load('cursosEstudiante', 'comentarios', 'conversaciones.usuarios', 'conversaciones.participantes');
        $token = $usuario->createToken('Token')->plainTextToken;

        // Generar y retornar un token de acceso
        return response()->json([
            'token' => $token,
            'usuario' => $usuario
        ], 200);
    }



    /**
     * Handle user logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Obtener el usuario actualmente autenticado
        $usuario = $request->user();

        // Verificar si el usuario está autenticado
        if (!$usuario) {
            return Response::json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Revocar el token de acceso actual
        $usuario->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Usuario desconectado exitosamente'
        ], 200);
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

            // Manejar la carga de la imagen
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('imagenes', 'public');
                $data['imagen'] = $path;
            }

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
                'imagen' => $data["imagen"],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Crear URL de verificación
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify', // Nombre de la ruta
                Carbon::now()->addHours(24), // Tiempo de expiración
                ['id' => $usuario->id, 'hash' => sha1($usuario->email)] // Parámetros de la ruta
            );

            // Enviar el correo de bienvenida
            Mail::to($usuario->email)->send(new RegistroUsuarioMailable($usuario, $verificationUrl));

            return response()->json([
                'message' => 'Usuario registrado y correo de confirmación enviado exitosamente',
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
}
