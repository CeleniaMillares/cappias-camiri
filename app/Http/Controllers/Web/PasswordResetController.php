<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function create(Request $request, string $token)
    {
        return view('auth.password-reset', [
            'token' => $token,
            'correo' => $request->query('email'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'correo' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            [
                'correo' => $validated['correo'],
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function ($user, string $password): void {
                $user->forceFill([
                    'contrasena_hash' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('success', 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.');
        }

        return back()->withErrors([
            'correo' => __($status),
        ]);
    }
}
