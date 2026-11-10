<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Venta;
use App\Models\Liquidacion;
use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiquidacionController extends Controller
{
    // 1. Lista de socios con su saldo pendiente e historial de pagos
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        // Si es Socio, solo ve su historial de pagos recibidos
        if ($user->hasRole('Socio/Instructor')) {
            $liquidaciones = Liquidacion::where('id_usuario_socio', $user->id_usuario)
                ->orderBy('created_at', 'desc')
                ->get();
            return view('admin.liquidaciones.socio_index', compact('liquidaciones'));
        }

        // ==== VISTA PARA LA DIRECTIVA ====
        
        // Capturamos los filtros
        $buscar = $request->input('buscar');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // 1. Calculamos los saldos pendientes de cada socio (Deuda global, no se filtra por fecha)
        $socios = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Socio/Instructor'))
            ->when($buscar, function($q) use ($buscar) {
                $q->where('nombre', 'LIKE', '%' . $buscar . '%');
            })->get();
        
        $saldos = [];
        foreach ($socios as $socio) {
            $ventas = Venta::where('estado', 'PAGADA')
                ->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $socio->id_usuario))
                ->get();
            
            $bruto = 0;
            foreach ($ventas as $v) {
                foreach ($v->detalles as $d) {
                    if ($d->producto->id_usuario_socio == $socio->id_usuario) {
                        $bruto += $d->subtotal;
                    }
                }
            }

            $comisionPorc = Parametro::where('clave', 'comision_venta')->value('valor') ?? 5;
            $netoGenerado = $bruto - ($bruto * $comisionPorc / 100);
            
            $netoPagado = Liquidacion::where('id_usuario_socio', $socio->id_usuario)
                ->where('estado', 'PAGADA')
                ->sum('monto_neto');

            $saldo = $netoGenerado - $netoPagado;

            // Solo mostramos al socio si se le debe dinero (saldo > 0.01)
            if ($saldo > 0.01) { 
                $saldos[] = [
                    'socio' => $socio,
                    'saldo' => $saldo
                ];
            }
        }

        // 2. Historial de pagos realizados (Filtrable por socio y por fechas)
        $historial = Liquidacion::with('socio')
            ->where('estado', 'PAGADA')
            ->when($buscar, function($q) use ($buscar) {
                $q->whereHas('socio', function($q2) use ($buscar) {
                    $q2->where('nombre', 'LIKE', '%' . $buscar . '%');
                });
            })
            ->when($fechaInicio, function($q) use ($fechaInicio) {
                $q->whereDate('created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function($q) use ($fechaFin) {
                $q->whereDate('created_at', '<=', $fechaFin);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.liquidaciones.index', compact('saldos', 'historial', 'buscar', 'fechaInicio', 'fechaFin'));
    }

    // 2. Formulario para registrar un pago (total o parcial)
    public function create($id_usuario_socio) {
        $socio = Usuario::findOrFail($id_usuario_socio);
        
        // Obtenemos las ventas pagadas que incluyen productos del socio
        $ventas = Venta::where('estado', 'PAGADA')
            ->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $id_usuario_socio))
            ->with('detalles.producto')->get();

        $bruto = 0;
        foreach ($ventas as $v) {
            foreach ($v->detalles as $d) {
                if ($d->producto->id_usuario_socio == $id_usuario_socio) {
                    $bruto += $d->subtotal;
                }
            }
        }

        $comisionPorc = Parametro::where('clave', 'comision_venta')->value('valor') ?? 5;
        $netoGenerado = $bruto - ($bruto * $comisionPorc / 100);
        
        $netoPagado = Liquidacion::where('id_usuario_socio', $id_usuario_socio)
            ->where('estado', 'PAGADA')
            ->sum('monto_neto');

        $saldoPendiente = $netoGenerado - $netoPagado;

        return view('admin.liquidaciones.create', compact('socio', 'saldoPendiente', 'ventas', 'bruto', 'comisionPorc', 'netoGenerado'));
    }

    // 3. Guardar el pago (parcial o total)
    public function store(Request $request, $id_usuario_socio) {
        $request->validate([
            'monto_pagar' => 'required|numeric|min:0.1'
        ]);

        $socio = Usuario::findOrFail($id_usuario_socio);

        // Recalculamos el saldo en el servidor por seguridad (evita que alteren el HTML)
        $ventas = Venta::where('estado', 'PAGADA')
            ->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $id_usuario_socio))->get();
        
        $bruto = 0;
        foreach ($ventas as $v) {
            foreach ($v->detalles as $d) {
                if ($d->producto->id_usuario_socio == $id_usuario_socio) {
                    $bruto += $d->subtotal;
                }
            }
        }

        $comisionPorc = Parametro::where('clave', 'comision_venta')->value('valor') ?? 5;
        $netoGenerado = $bruto - ($bruto * $comisionPorc / 100);
        
        $netoPagado = Liquidacion::where('id_usuario_socio', $id_usuario_socio)->where('estado', 'PAGADA')->sum('monto_neto');
        $saldoPendiente = $netoGenerado - $netoPagado;

        // Validación: no puede pagar más de lo que se le debe
        if ($request->monto_pagar > $saldoPendiente + 0.01) {
            return back()->withErrors(['monto_pagar' => 'El monto a pagar no puede ser mayor al saldo pendiente (Bs. ' . number_format($saldoPendiente, 2) . ')']);
        }

        DB::transaction(function () use ($request, $socio, $comisionPorc, $netoGenerado, $saldoPendiente) {
            
            // Calculamos el bruto y comisión proporcional solo para el monto que se está abonando hoy
            $montoNetoAbono = $request->monto_pagar;
            $montoBrutoAbono = $montoNetoAbono / (1 - ($comisionPorc / 100));
            $comisionAbono = $montoBrutoAbono - $montoNetoAbono;

            // Registramos el abono en la tabla de liquidaciones
            Liquidacion::create([
                'id_usuario_socio' => $socio->id_usuario,
                'periodo_inicio' => now()->startOfMonth(),
                'periodo_fin' => now(),
                'monto_bruto' => $montoBrutoAbono,
                'comision' => $comisionAbono,
                'monto_neto' => $montoNetoAbono,
                'estado' => 'PAGADA',
            ]);

            // Si con este abono se cubre el 100% del saldo, marcamos las ventas como LIQUIDADAS
            if ($request->monto_pagar >= $saldoPendiente - 0.01) {
                Venta::where('estado', 'PAGADA')
                    ->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $socio->id_usuario))
                    ->update(['estado' => 'LIQUIDADA']);
            }
        });

        return redirect('/admin/liquidaciones')->with('success', 'Pago registrado correctamente.');
    }
}