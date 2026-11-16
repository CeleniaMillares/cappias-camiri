<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
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

        $usuario = Usuario::where('correo', $credentials['correo'])->first();
        if ($usuario && $usuario->hasRole('Alumno')) {
            return back()->withErrors([
                'correo' => 'Las cuentas con rol Alumno no pueden iniciar sesión.',
            ])->onlyInput('correo');
        }

        if (Auth::attempt([
            'correo' => $credentials['correo'],
            'password' => $credentials['contrasena'],
            'activo' => true,
        ])) {
            // Regenerar la sesión por seguridad (evita ataques de fijación de sesión)
            $request->session()->regenerate();

            // Redirigir al Dashboard
            return redirect()->intended('/admin/dashboard');
        }

        if ($usuario && ! $usuario->activo) {
            return back()->withErrors([
                'correo' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ])->onlyInput('correo');
        }

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