<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    // Mostrar perfil del socio
    public function socio() {
        $user = Auth::user();
        $socio = $user->socio;
        
        if (!$socio) {
            abort(403, 'No tienes un perfil de socio registrado.');
        }

        $pagos = $socio->cuotas()->orderBy('fecha_pago', 'desc')->get();
        
        return view('admin.perfil.socio', compact('user', 'socio', 'pagos'));
    }

    // Actualizar datos del socio
    public function updateSocio(Request $request) {
        $user = Auth::user();
        $socio = $user->socio;

        $request->validate([
            'nombre' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo,' . $user->id_usuario . ',id_usuario',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
        ]);

        $socio->update([
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
        ]);

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    // Cambiar contraseña
    public function updatePassword(Request $request) {
        $request->validate([
            'contrasena_actual' => 'required',
            'contrasena_nueva' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->contrasena_actual, $user->contrasena_hash)) {
            return back()->withErrors(['contrasena_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->update([
            'contrasena_hash' => Hash::make($request->contrasena_nueva),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }

    // Portal del Alumno/Cliente (ver sus inscripciones y compras)
    public function alumno() {
        $user = Auth::user();
        $alumno = $user->alumno;
        $inscripciones = $alumno
            ? $alumno->inscripciones()->with(['horarioCurso.curso', 'pagos'])->latest()->get()
            : collect();

        // También buscamos ventas hechas con su correo (aunque el sistema no vincula ventas a usuarios)
        // Por ahora solo mostramos inscripciones
        return view('admin.perfil.alumno', compact('user', 'inscripciones'));
    }
}