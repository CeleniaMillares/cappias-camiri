<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Muestra el formulario de login
    public function showLogin() 
    {
        // Si el usuario ya está logueado, lo mandamos al dashboard
        if (Auth::check()) {
            return redirect('/admin/dashboard');
        }
        
        return view('auth.login');
    }

    // Procesa el formulario
    public function login(Request $request) 
    {
        $credentials = $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required'
        ]);

        // Intentar autenticar con los campos personalizados (correo y contrasena_hash)
        if (Auth::attempt(['correo' => $credentials['correo'], 'password' => $credentials['contrasena']])) {
            // Regenerar la sesión por seguridad (evita ataques de fijación de sesión)
            $request->session()->regenerate();

            // Redirigir al Dashboard
            return redirect()->intended('/admin/dashboard');
        }

        // Si falla, volver atrás con error
        return back()->withErrors([
            'correo' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('correo');
    }

    // Cierra la sesión
    public function logout(Request $request) 
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}