<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Venta;
use App\Models\PagoSocio;
use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoSocioController extends Controller
{
    // 1. Lista de socios con su saldo pendiente e historial de pagos
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        // Si es Socio, solo ve su historial de pagos recibidos
        if ($user->hasRole('Socio/Instructor')) {
            $pagos = PagoSocio::where('id_usuario_socio', $user->id_usuario)
                ->orderBy('created_at', 'desc')
                ->get();
            return view('admin.pagos_socios.socio_index', compact('pagos'));
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

        $comisionPorc = (float) (Parametro::where('clave', 'comision_venta')->value('valor') ?? 5);
        $socioIds = $socios->pluck('id_usuario');
        $brutosPorSocio = [];
        $pagadoPorSocio = [];

        if ($socioIds->isNotEmpty()) {
            $ventas = Venta::where('estado', 'PAGADA')
                ->whereHas('detalles.producto', fn($q) => $q->whereIn('id_usuario_socio', $socioIds))
                ->with(['detalles.producto'])
                ->get();

            foreach ($ventas as $venta) {
                foreach ($venta->detalles as $detalle) {
                    $idSocio = $detalle->producto->id_usuario_socio;
                    if ($socioIds->contains($idSocio)) {
                        $brutosPorSocio[$idSocio] = ($brutosPorSocio[$idSocio] ?? 0) + (float) $detalle->subtotal;
                    }
                }
            }

            $pagadoPorSocio = PagoSocio::whereIn('id_usuario_socio', $socioIds)
                ->where('estado', 'PAGADA')
                ->selectRaw('id_usuario_socio, SUM(monto_neto) as total_pagado')
                ->groupBy('id_usuario_socio')
                ->pluck('total_pagado', 'id_usuario_socio')
                ->all();
        }

        $saldos = [];
        foreach ($socios as $socio) {
            $bruto = (float) ($brutosPorSocio[$socio->id_usuario] ?? 0);
            $netoGenerado = round($bruto - ($bruto * $comisionPorc / 100), 2);
            $netoPagado = (float) ($pagadoPorSocio[$socio->id_usuario] ?? 0);
            $saldo = round($netoGenerado - $netoPagado, 2);

            // Solo mostramos al socio si se le debe dinero (saldo > 0.01)
            if ($saldo > 0) {
                $saldos[] = [
                    'socio' => $socio,
                    'saldo' => $saldo
                ];
            }
        }

        // 2. Historial de pagos realizados (Filtrable por socio y por fechas)
        $historial = PagoSocio::with('socio')
            ->whereIn('estado', ['PAGADA', 'ANULADA'])
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

        return view('admin.pagos_socios.index', compact('saldos', 'historial', 'buscar', 'fechaInicio', 'fechaFin'));
    }

    // 2. Formulario para registrar un pago (total o parcial)
    public function create($id_usuario_socio) {
        $socio = Usuario::findOrFail($id_usuario_socio);
        
        // Obtenemos las ventas pagadas que incluyen productos del socio
        $ventas = Venta::where('estado', 'PAGADA')
            ->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $id_usuario_socio))
            ->with(['detalles.producto'])->get();

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
        
        $netoPagado = PagoSocio::where('id_usuario_socio', $id_usuario_socio)
            ->where('estado', 'PAGADA')
            ->sum('monto_neto');

        $saldoPendiente = round($netoGenerado - $netoPagado, 2);

        return view('admin.pagos_socios.create', compact('socio', 'saldoPendiente', 'ventas', 'bruto', 'comisionPorc', 'netoGenerado'));
    }

    // 3. Guardar el pago (parcial o total)
    public function store(Request $request, $id_usuario_socio) {
        $request->validate([
            'monto_pagar' => 'required|numeric|decimal:0,2|min:0.01',
            'metodo' => 'required|in:EFECTIVO,QR',
        ]);

        $resultado = DB::transaction(function () use ($request, $id_usuario_socio) {
            $socio = Usuario::whereKey($id_usuario_socio)->lockForUpdate()->firstOrFail();
            $ventas = Venta::where('estado', 'PAGADA')
                ->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $socio->id_usuario))
                ->with(['detalles.producto'])
                ->lockForUpdate()
                ->get();

            $bruto = 0;
            foreach ($ventas as $venta) {
                foreach ($venta->detalles as $detalle) {
                    if ($detalle->producto->id_usuario_socio === $socio->id_usuario) {
                        $bruto += (float) $detalle->subtotal;
                    }
                }
            }

            $comisionPorc = (float) (Parametro::where('clave', 'comision_venta')->value('valor') ?? 5);
            if ($comisionPorc < 0 || $comisionPorc >= 100) {
                throw new \LogicException('El porcentaje de comisión de ventas debe ser mayor o igual a 0 y menor a 100.');
            }

            $netoGenerado = round($bruto - ($bruto * $comisionPorc / 100), 2);
            $netoPagado = (float) PagoSocio::where('id_usuario_socio', $socio->id_usuario)
                ->where('estado', 'PAGADA')
                ->sum('monto_neto');
            $saldoPendiente = round($netoGenerado - $netoPagado, 2);
            $montoNetoAbono = round((float) $request->monto_pagar, 2);

            if ($montoNetoAbono > $saldoPendiente) {
                return ['saldo' => $saldoPendiente];
            }

            if ($saldoPendiente <= 0) {
                return ['saldo' => $saldoPendiente];
            }

            $montoBrutoAbono = round($montoNetoAbono / (1 - ($comisionPorc / 100)), 2);
            $comisionAbono = round($montoBrutoAbono - $montoNetoAbono, 2);

            PagoSocio::create([
                'id_usuario_socio' => $socio->id_usuario,
                'periodo_inicio' => now()->startOfMonth(),
                'periodo_fin' => now(),
                'monto_bruto' => $montoBrutoAbono,
                'comision' => $comisionAbono,
                'monto_neto' => $montoNetoAbono,
                'metodo' => $request->metodo,
                'estado' => 'PAGADA',
            ]);

            return ['saldo' => null];
        });

        if ($resultado['saldo'] !== null) {
            return back()->withErrors([
                'monto_pagar' => 'El monto no puede ser mayor al saldo pendiente. Saldo actual: Bs. '
                    . number_format(max(0, $resultado['saldo']), 2),
            ])->withInput();
        }

        return redirect('/admin/pagos-socios')->with('success', 'Pago registrado correctamente.');
    }

    public function anular($id) {
        $anulada = DB::transaction(function () use ($id) {
            $pago = PagoSocio::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($pago->estado !== 'PAGADA') {
                return false;
            }

            $pago->update(['estado' => 'ANULADA']);
            return true;
        });

        if (!$anulada) {
            return back()->with('error', 'Solo se pueden anular pagos a socios pagados.');
        }

        return back()->with('success', 'Pago a socio anulado correctamente.');
    }
}