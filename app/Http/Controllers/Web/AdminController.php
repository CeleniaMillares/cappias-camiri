<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Socio;
use App\Models\Rol;
use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function usuarios() {
        $usuarios = Usuario::with('roles')->get();
        return view('admin.usuarios', compact('usuarios'));
    }

    public function createUsuario() {
        $roles = Rol::whereIn('nombre', ['Administrador', 'Alumno', 'Cliente'])->get();
        return view('admin.crear_usuario', compact('roles'));
    }

    public function storeUsuario(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|min:6',
            'roles' => 'required|array'
        ]);

        $rolSocio = Rol::where('nombre', 'Socio/Instructor')->first();
        if ($rolSocio && in_array($rolSocio->id_rol, $request->roles)) {
            return back()->withErrors(['roles' => 'No puedes crear un Socio desde aquí. Usa el módulo de Socios.'])->withInput();
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'contrasena_hash' => Hash::make($request->contrasena),
            'activo' => true
        ]);

        $usuario->roles()->attach($request->roles);

        return redirect('/admin/usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    public function promoverSocios() {
        $socios = Socio::with(['usuario.roles'])->get();
        $rolDirectiva = Rol::where('nombre', 'Directiva')->first();
        return view('admin.promover_socio', compact('socios', 'rolDirectiva'));
    }

    public function asignarDirectiva($id_socio) {
        $socio = Socio::with('usuario')->findOrFail($id_socio);
        $rolDirectiva = Rol::where('nombre', 'Directiva')->first();
        if ($socio->usuario->hasRole('Directiva')) {
            return back()->with('error', 'El socio ya es Directiva.');
        }

        $socio->usuario->roles()->syncWithoutDetaching([$rolDirectiva->id_rol]);

        \App\Models\Auditoria::create([
            'id_usuario' => Auth::user()->id_usuario,
            'nombre_usuario' => Auth::user()->nombre ?? 'Sistema',
            'accion' => 'PROMOVER',
            'entidad' => 'Usuario: ' . $socio->usuario->nombre . ' a Directiva'
        ]);

        return back()->with('success', 'El socio ' . $socio->usuario->nombre . ' ahora también es Directiva.');
    }

    public function retirarDirectiva($id_socio) {
        $socio = Socio::with('usuario')->findOrFail($id_socio);
        $rolDirectiva = Rol::where('nombre', 'Directiva')->first();
        if (! $socio->usuario->hasRole('Directiva')) {
            return back()->with('error', 'El socio no es Directiva.');
        }

        $socio->usuario->roles()->detach($rolDirectiva->id_rol);

        \App\Models\Auditoria::create([
            'id_usuario' => Auth::user()->id_usuario,
            'nombre_usuario' => Auth::user()->nombre ?? 'Sistema',
            'accion' => 'RETIRAR',
            'entidad' => 'Cargo de Directiva a: ' . $socio->usuario->nombre
        ]);

        return back()->with('success', 'El cargo de Directiva ha sido retirado. ' . $socio->usuario->nombre . ' sigue siendo Socio.');
    }

    public function parametros() {
        $parametros = Parametro::all();
        return view('admin.parametros', compact('parametros'));
    }

    public function updateParametros(Request $request) {
        foreach($request->parametros as $id => $valor) {
            Parametro::where('id_parametro', $id)->update(['valor' => $valor]);
        }
        return redirect('/admin/parametros')->with('success', 'Parámetros actualizados correctamente.');
    }

    public function uploadQr(Request $request) {
        $request->validate([
            'qr_image' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('qr_image')) {
            $qrAnterior = Parametro::where('clave', 'qr_image_url')->value('valor');
            $path = $request->file('qr_image')->store('qr', 'public');
            Parametro::updateOrCreate(
                ['clave' => 'qr_image_url'],
                ['valor' => $path, 'descripcion' => 'Ruta de la imagen del QR de pagos']
            );

            if ($qrAnterior && Storage::disk('public')->exists($qrAnterior)) {
                Storage::disk('public')->delete($qrAnterior);
            }
        }
        return back()->with('success', 'Imagen del QR oficial actualizada correctamente.');
    }

    public function auditoria() {
        $registros = \App\Models\Auditoria::orderBy('created_at', 'desc')->take(100)->get();
        return view('admin.auditoria', compact('registros'));
    }

    public function editUsuario(string $id) {
        $usuario = Usuario::with('roles')->findOrFail($id);
        $roles = Rol::whereIn('nombre', ['Administrador', 'Directiva', 'Alumno', 'Cliente'])->get();
        return view('admin.editar_usuario', compact('usuario', 'roles'));
    }

    public function updateUsuario(Request $request, string $id) {
        $usuario = Usuario::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo,' . $id . ',id_usuario',
            'roles' => 'required|array'
        ]);

        $usuario->update([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'activo' => $request->has('activo') ? true : false,
        ]);

        if ($request->filled('contrasena')) {
            $usuario->update(['contrasena_hash' => Hash::make($request->contrasena)]);
        }

        $usuario->roles()->sync($request->roles);
        return redirect('/admin/usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroyUsuario(string $id) {
        if (Auth::user()->id_usuario == $id) {
            return back()->with('error', 'Seguridad: No puedes desactivar tu propia cuenta.');
        }
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['activo' => false]);
        return redirect('/admin/usuarios')->with('success', 'Usuario desactivado.');
    }
}