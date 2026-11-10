<?php
namespace App\Http\Controllers;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index() {
        // Cargamos la categoría y el socio para que se vea en la respuesta
        return response()->json(Producto::with(['categoria', 'socio'])->where('activo', true)->get());
    }

    public function store(Request $request) {
        $request->validate([
            'id_categoria' => 'required|uuid|exists:categorias,id_categoria',
            'id_usuario_socio' => 'required|uuid|exists:usuarios,id_usuario',
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
        ]);

        $producto = Producto::create($request->all());
        return response()->json($producto, 201);
    }

    public function show($id) {
        return response()->json(Producto::with(['categoria', 'socio'])->findOrFail($id));
    }

    public function update(Request $request, $id) {
        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        return response()->json($producto);
    }

    public function destroy($id) {
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => false]); // Borrado lógico
        return response()->json(['mensaje' => 'Producto desactivado']);
    }
}