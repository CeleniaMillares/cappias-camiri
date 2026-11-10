<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Feria;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\Parametro; // 🔥 IMPORTANTE
use App\Events\VentaConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        $query = Venta::with(['directiva', 'feria', 'detalles.producto', 'pagos']);

        if ($user->hasRole('Socio/Instructor')) {
            $query->whereHas('detalles.producto', fn($q) => $q->where('id_usuario_socio', $user->id_usuario));
        }
        if ($request->filled('fecha_inicio')) $query->whereDate('created_at', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin')) $query->whereDate('created_at', '<=', $request->fecha_fin);
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('origen')) {
            if ($request->origen === 'sede') $query->whereNull('id_feria');
            elseif ($request->origen === 'feria') $query->whereNotNull('id_feria');
        }
        if ($request->filled('producto')) {
            $query->whereHas('detalles.producto', fn($q) => $q->where('nombre', 'LIKE', '%' . $request->producto . '%'));
        }

        $ventas = $query->orderBy('created_at', 'desc')->get();
        $totalFiltrado = $ventas->whereIn('estado', ['PAGADA', 'LIQUIDADA'])->sum('total');
        $totalPendiente = $ventas->where('estado', 'PENDIENTE_PAGO')->sum('total');

        return view('admin.ventas.index', compact('ventas', 'totalFiltrado', 'totalPendiente'));
    }

    public function create() {
        $productos = Producto::with('socio')->where('activo', true)->where('stock_actual', '>', 0)->get();
        $ferias = Feria::all();
        // 🔥 Cargamos el QR oficial
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor'); 
        return view('admin.ventas.create', compact('productos', 'ferias', 'qrImage'));
    }

    public function store(Request $request) {
        $request->validate([
            'id_feria' => 'nullable|exists:ferias,id_feria',
            'metodo_pago' => 'required|in:EFECTIVO,QR,MIXTO',
            'monto_efectivo' => 'required_if:metodo_pago,MIXTO|nullable|numeric|min:0',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $estadoVenta = 'PENDIENTE_PAGO';
                if ($request->metodo_pago === 'EFECTIVO' || $request->has('confirmar_qr')) {
                    $estadoVenta = 'PAGADA';
                }

                $venta = Venta::create([
                    'id_feria' => $request->id_feria,
                    'id_usuario_directiva' => Auth::user()->id_usuario,
                    'total' => 0,
                    'estado' => $estadoVenta,
                ]);

                $totalVenta = 0;
                foreach ($request->productos as $item) {
                    $producto = Producto::findOrFail($item['id_producto']);
                    if ($producto->stock_actual < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para: {$producto->nombre}");
                    }
                    $subtotal = $producto->precio * $item['cantidad'];
                    $totalVenta += $subtotal;

                    DetalleVenta::create([
                        'id_venta' => $venta->id_venta,
                        'id_producto' => $producto->id_producto,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $subtotal,
                    ]);
                }

                $venta->update(['total' => $totalVenta]);

                // 🔥 REGISTRO DE PAGOS
                $montoEfectivo = 0;
                if ($request->metodo_pago === 'EFECTIVO') { $montoEfectivo = $totalVenta; }
                elseif ($request->metodo_pago === 'MIXTO') { $montoEfectivo = $request->monto_efectivo; }

                if ($montoEfectivo > 0) {
                    Pago::create([
                        'id_venta' => $venta->id_venta,
                        'monto' => $montoEfectivo,
                        'metodo' => 'EFECTIVO',
                        'estado' => 'CONFIRMADO',
                    ]);
                }

                // 🔥 Si es QR y se confirma, guardamos el pago QR
                if (in_array($request->metodo_pago, ['QR', 'MIXTO']) && $request->has('confirmar_qr')) {
                    $saldoQR = $totalVenta - $montoEfectivo;
                    if ($saldoQR > 0) {
                        Pago::create([
                            'id_venta' => $venta->id_venta,
                            'monto' => $saldoQR,
                            'metodo' => 'QR',
                            'estado' => 'CONFIRMADO',
                        ]);
                    }
                }

                if ($estadoVenta === 'PAGADA') {
                    event(new VentaConfirmada($venta));
                }
            });

            return redirect('/admin/ventas')->with('success', 'Venta registrada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function confirmarPago($id_venta) {
        $venta = Venta::findOrFail($id_venta);
        if ($venta->estado === 'PAGADA' || $venta->estado === 'LIQUIDADA') {
            return back()->with('error', 'Esta venta ya está pagada.');
        }

        DB::transaction(function () use ($venta) {
            $totalPagado = Pago::where('id_venta', $venta->id_venta)->where('estado', 'CONFIRMADO')->sum('monto');
            $saldo = $venta->total - $totalPagado;

            if ($saldo > 0) {
                Pago::create([
                    'id_venta' => $venta->id_venta,
                    'monto' => $saldo,
                    'metodo' => 'QR',
                    'estado' => 'CONFIRMADO',
                ]);
            }

            $venta->update(['estado' => 'PAGADA']);
            event(new VentaConfirmada($venta));
        });

        return back()->with('success', 'Pago confirmado. La venta está pagada y el stock descontado.');
    }
}