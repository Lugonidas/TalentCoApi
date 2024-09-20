<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verificar el hash del email
        if (!hash_equals(sha1($user->email), $hash)) {
            return response()->json(['message' => 'Verificación fallida'], 400);
        }

        // Verificar si el correo electrónico ya ha sido verificado
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya ha sido verificado'], 200);
        }

        // Marcar el correo como verificado
        $user->markEmailAsVerified();

        return response()->json(['message' => 'Correo verificado exitosamente'], 200);
    }
}
