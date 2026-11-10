<?php
namespace App\Http\Controllers;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Events\VentaConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validamos los datos de entrada
        $request->validate([
            'id_feria' => 'nullable|uuid|exists:ferias,id_feria',
            'id_usuario_directiva' => 'required|uuid|exists:usuarios,id_usuario',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|uuid|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        // 2. Usamos una transacción para asegurar que si algo falla, no se guarde nada a medias
        return DB::transaction(function () use ($request) {
            
            // Creamos la cabecera de la venta (estado PENDIENTE inicialmente)
            $venta = Venta::create([
                'id_feria' => $request->id_feria,
                'id_usuario_directiva' => $request->id_usuario_directiva,
                'total' => 0, 
                'estado' => 'PAGADA', // Asumimos que se paga al momento
            ]);

            $totalVenta = 0;

            // Recorremos los productos vendidos
            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['id_producto']);
                
                // Validamos el stock (Regla de Negocio)
                if ($producto->stock_actual < $item['cantidad']) {
                    abort(400, "Stock insuficiente para el producto: {$producto->nombre}");
                }

                $subtotal = $producto->precio * $item['cantidad'];
                $totalVenta += $subtotal;

                // Guardamos el detalle
                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
            }

            // Actualizamos el total de la venta
            $venta->update(['total' => $totalVenta]);

            // 🔥 DISPARAMOS EL EVENTO (Aquí se ejecutan los listeners automáticos)
            event(new VentaConfirmada($venta));

            return response()->json([
                'mensaje' => 'Venta registrada exitosamente',
                'venta' => $venta->load('detalles')
            ], 201);
        });
    }
}
