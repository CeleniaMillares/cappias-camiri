<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Socio;
use App\Models\Rol;
use App\Models\CuotaSocio;
use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SocioController extends Controller
{
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        if ($user->hasRole('Socio/Instructor')) {
            return redirect('/admin/mi-perfil-socio');
        }

        $buscar = $request->input('buscar');
        $socios = Socio::with(['usuario'])
            ->when($buscar, function($q) use ($buscar) {
                $q->whereHas('usuario', fn($q2) => $q2->where('nombre', 'LIKE', '%' . $buscar . '%'))
                  ->orWhere('ci', 'LIKE', '%' . $buscar . '%');
            })->get();

        $mesActual = now()->format('Y-m');
        
        foreach ($socios as $socio) {
            $socio->pago_mes_actual = $socio->cuotas()
                ->where('tipo', 'MENSUALIDAD')
                ->where('periodo', $mesActual)
                ->where('estado', 'CONFIRMADO')
                ->exists();

            $socio->pago_pendiente = $socio->cuotas()
                ->where('estado', 'PENDIENTE')
                ->first();
        }

        return view('admin.socios.index', compact('socios', 'buscar'));
    }

    public function create() {
        $montoInscripcion = Parametro::where('clave', 'monto_inscripcion')->value('valor') ?? 100;
        return view('admin.socios.create', compact('montoInscripcion'));
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|min:6',
            'ci' => 'required|string|max:20|unique:socios,ci',
            'telefono' => 'required|string|max:20',
            'metodo_pago' => 'required|in:EFECTIVO,QR,MIXTO',
            'monto_efectivo' => 'required_if:metodo_pago,MIXTO|nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $rolSocio = Rol::where('nombre', 'Socio/Instructor')->first();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'contrasena_hash' => Hash::make($request->contrasena),
                'activo' => true,
            ]);
            $usuario->roles()->attach($rolSocio->id_rol);

            $socio = Socio::create([
                'id_usuario' => $usuario->id_usuario,
                'ci' => $request->ci,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'fecha_inscripcion' => now()->toDateString(),
                'estado' => 'ACTIVO'
            ]);

            $estado = 'PENDIENTE';
            if ($request->metodo_pago === 'EFECTIVO' || $request->has('confirmar_qr')) {
                $estado = 'CONFIRMADO';
            }

            $montoInscripcion = Parametro::where('clave', 'monto_inscripcion')->value('valor') ?? 100;
            
            CuotaSocio::create([
                'id_socio' => $socio->id_socio,
                'tipo' => 'MATRICULA', // 🔥 Cambiado
                'metodo' => $request->metodo_pago,
                'estado' => $estado,
                'monto' => $montoInscripcion,
                'fecha_pago' => now()->toDateString(),
            ]);
        });

        return redirect('/admin/socios')->with('success', 'Socio inscrito correctamente.');
    }

    public function createPagoMensual($id_socio) {
        $socio = Socio::with('usuario')->findOrFail($id_socio);
        $montoMensualidad = Parametro::where('clave', 'monto_mensualidad')->value('valor') ?? 50;
        return view('admin.socios.pago_mensual', compact('socio', 'montoMensualidad'));
    }

    public function storePagoMensual(Request $request, $id_socio) {
        $request->validate([
            'periodo_inicio' => 'required|date_format:Y-m',
            'meses' => 'required|integer|min:1|max:12',
            'metodo_pago' => 'required|in:EFECTIVO,QR,MIXTO',
            'monto_efectivo' => 'required_if:metodo_pago,MIXTO|nullable|numeric|min:0',
        ]);

        $socio = Socio::findOrFail($id_socio);
        $montoMensualidad = Parametro::where('clave', 'monto_mensualidad')->value('valor') ?? 50;
        $meses = $request->meses;
        
        $estado = 'PENDIENTE';
        if ($request->metodo_pago === 'EFECTIVO' || $request->has('confirmar_qr')) {
            $estado = 'CONFIRMADO';
        }

        $fechaInicio = Carbon::createFromFormat('Y-m', $request->periodo_inicio);

        DB::transaction(function () use ($socio, $fechaInicio, $meses, $montoMensualidad, $request, $estado) {
            for ($i = 0; $i < $meses; $i++) {
                $periodoActual = $fechaInicio->copy()->addMonths($i)->format('Y-m');

                $existe = CuotaSocio::where('id_socio', $socio->id_socio)
                    ->where('tipo', 'MENSUALIDAD') // 🔥 Cambiado
                    ->where('periodo', $periodoActual)
                    ->where('estado', 'CONFIRMADO')
                    ->exists();

                if (!$existe) {
                    CuotaSocio::create([
                        'id_socio' => $socio->id_socio,
                        'tipo' => 'MENSUALIDAD', // 🔥 Cambiado
                        'metodo' => $request->metodo_pago,
                        'estado' => $estado,
                        'periodo' => $periodoActual,
                        'monto' => $montoMensualidad,
                        'fecha_pago' => now()->toDateString(),
                    ]);
                }
            }
        });

        return redirect('/admin/socios')->with('success', "Se registraron $meses mes(es) de mensualidad correctamente.");
    }

    public function confirmarPago($id_cuota) {
        $cuota = CuotaSocio::findOrFail($id_cuota);
        
        if ($cuota->estado === 'CONFIRMADO') {
            return back()->with('error', 'Este pago ya estaba confirmado.');
        }

        $cuota->update(['estado' => 'CONFIRMADO']);

        return back()->with('success', 'Pago QR confirmado correctamente.');
    }

    public function historial($id_socio) {
        $socio = Socio::with(['usuario'])->findOrFail($id_socio);
        $cuotas = $socio->cuotas()->orderBy('fecha_pago', 'desc')->get();
        return view('admin.socios.historial', compact('socio', 'cuotas'));
    }
}