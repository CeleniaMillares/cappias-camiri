<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoPublicoController extends Controller
{
    // Muestra la pantalla de pago al cliente
    public function show($id_venta) {
        $venta = Venta::with(['detalles.producto', 'pagos'])->findOrFail($id_venta);
        
        // Calculamos cuánto ha pagado y cuánto debe
        $totalPagado = $venta->pagos->where('estado', 'CONFIRMADO')->sum('monto');
        $saldo = $venta->total - $totalPagado;

        return view('publico.pagar', compact('venta', 'saldo', 'totalPagado'));
    }

    // Guarda el comprobante subido por el cliente
    public function store(Request $request, $id_venta) {
        $request->validate([
            'comprobante' => 'required|image|max:2048',
            'monto' => 'required|numeric|min:0.1'
        ]);

        $venta = Venta::findOrFail($id_venta);

        // Guardamos la imagen
        $path = $request->file('comprobante')->store('comprobantes', 'public');

        // Registramos el pago en estado PENDIENTE (la directiva debe aprobarlo después)
        Pago::create([
            'id_venta' => $venta->id_venta,
            'monto' => $request->monto,
            'metodo' => 'QR',
            'comprobante_url' => $path,
            'estado' => 'PENDIENTE' // 🔥 Queda pendiente para que la directiva lo revise
        ]);

        return back()->with('success', '¡Comprobante subido con éxito! Por favor, espera a que la directiva verifique tu pago.');
    }
}