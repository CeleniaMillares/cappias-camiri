<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $idSocio = $socio->id_socio;
        $ferias = Feria::query()
            ->whereDate('fecha', '>=', now()->toDateString())
            ->with(['socios' => fn ($query) => $query->where('socios.id_socio', $idSocio)])
            ->orderBy('fecha')
            ->get();

        return view('admin.perfil.socio', compact('user', 'socio', 'pagos', 'ferias'));
    }

    public function solicitarParticipacionFeria(string $id)
    {
        $socio = Auth::user()->socio;
        if (! $socio) {
            abort(403, 'No tienes un perfil de socio registrado.');
        }

        $resultado = DB::transaction(function () use ($id, $socio) {
            $feria = Feria::query()->lockForUpdate()->findOrFail($id);
            if (Carbon::parse($feria->fecha)->isBefore(today())) {
                abort(404, 'La feria ya no está disponible para solicitudes.');
            }

            $participacion = $feria->socios()
                ->wherePivot('id_socio', $socio->id_socio)
                ->first();

            if ($participacion) {
                return ['existente' => true, 'estado' => $participacion->pivot->estado];
            }

            $feria->socios()->attach($socio->id_socio, ['estado' => 'PENDIENTE']);

            return ['existente' => false, 'estado' => 'PENDIENTE'];
        });

        $mensaje = $resultado['existente']
            ? "Ya tienes una participación en esta feria con estado {$resultado['estado']}."
            : 'Tu solicitud para participar en la feria fue registrada.';

        return back()->with('success', $mensaje);
    }

    // Actualizar datos del socio
    public function updateSocio(Request $request) {
        $user = Auth::user();
        $socio = $user->socio;

        $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'correo' => 'required|email|unique:usuarios,correo,' . $user->id_usuario . ',id_usuario',
            'telefono' => ['required', 'string', 'regex:/^[0-9]{7,8}$/'],
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

}