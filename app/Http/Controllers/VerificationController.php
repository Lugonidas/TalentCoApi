<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verificar el hash del email
        if (!hash_equals(sha1($user->email), $hash)) {
            return response()->json(['message' => 'Verificación fallida'], 400);
        }

        // Verificar si el enlace no ha expirado
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'El enlace de verificación ha expirado'], 400);
        }

        // Verificar el correo electrónico
        if (!$user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $user->save();
        }

        return response()->json(['message' => 'Correo verificado']);
    }
}
