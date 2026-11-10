<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * RF-14: Iniciar sesión
     * Valida credenciales y devuelve un token de acceso Sanctum.
     */
    public function login(Request $request)
    {
        // 1. Validación de campos
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required|string',
        ]);

        // 2. Buscar al usuario por correo
        $usuario = Usuario::where('correo', $request->correo)->first();

        // 3. Validar que exista, esté activo y la contraseña coincida
        if (!$usuario || !$usuario->activo || !Hash::check($request->contrasena, $usuario->contrasena_hash)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales no coinciden o el usuario está inactivo.'],
            ]);
        }

        // 4. Eliminar tokens anteriores (opcional pero recomendado: un solo token por dispositivo)
        $usuario->tokens()->delete();

        // 5. Crear un nuevo token
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // 6. Obtener los roles del usuario
        $roles = $usuario->roles->pluck('nombre');

        return response()->json([
            'mensaje' => 'Sesión iniciada correctamente.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'roles' => $roles,
            ],
        ]);
    }

    /**
     * Cerrar sesión (opcional, aunque el usuario dijo que no lo necesita,
     * es buena práctica incluirlo para que los tokens se revoquen).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Obtener el perfil del usuario autenticado.
     */
    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user()->load('roles'),
        ]);
    }
}