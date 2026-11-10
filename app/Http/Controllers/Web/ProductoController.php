<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // 1. Listar productos
    public function index(Request $request) {
        /** @var \App\Models\Usuario $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        // Iniciamos la consulta
        $query = Producto::with(['categoria', 'socio'])->where('activo', true);

        // Si es Socio, solo ve sus productos
        if ($user->hasRole('Socio/Instructor')) {
            $query->where('id_usuario_socio', $user->id_usuario);
        }

        // Filtro por Nombre
        if ($request->filled('buscar')) {
            $query->where('nombre', 'LIKE', '%' . $request->buscar . '%');
        }

        // Filtro por Categoría
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        // Filtro por Socio
        if ($request->filled('id_usuario_socio')) {
            $query->where('id_usuario_socio', $request->id_usuario_socio);
        }

        $productos = $query->get();

        // Datos para los selectores de los filtros
        $categorias = \App\Models\Categoria::where('activo', true)->get();
        $socios = \App\Models\Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Socio/Instructor'))->get();

        return view('admin.productos.index', compact('productos', 'categorias', 'socios'));
    }

    // 2. Mostrar formulario de creación
    public function create() {
        $categorias = Categoria::where('activo', true)->get();
        // Solo mostramos usuarios con rol Socio/Instructor
        $socios = Usuario::whereHas('roles', function($q) {
            $q->where('nombre', 'Socio/Instructor');
        })->get();

        return view('admin.productos.create', compact('categorias', 'socios'));
    }

    // 3. Guardar nuevo producto
    public function store(Request $request) {
        $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_usuario_socio' => 'required|exists:usuarios,id_usuario',
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
        ]);

        Producto::create([
            'id_categoria' => $request->id_categoria,
            'id_usuario_socio' => $request->id_usuario_socio,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'materiales' => $request->materiales,
            'tecnica' => $request->tecnica,
            'stock_actual' => $request->stock_actual,
            'stock_minimo' => $request->stock_minimo,
            'activo' => true,
        ]);

        return redirect('/admin/productos')->with('success', 'Producto registrado correctamente.');
    }

    // 4. Mostrar formulario de edición (NUEVO)
    public function edit($id) {
        $producto = Producto::findOrFail($id);
        $categorias = Categoria::where('activo', true)->get();
        $socios = Usuario::whereHas('roles', function($q) {
            $q->where('nombre', 'Socio/Instructor');
        })->get();
        
        return view('admin.productos.edit', compact('producto', 'categorias', 'socios'));
    }

    // 5. Actualizar producto (NUEVO)
    public function update(Request $request, $id) {
        $producto = Producto::findOrFail($id);
        
        $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_usuario_socio' => 'required|exists:usuarios,id_usuario',
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
        ]);

        $producto->update($request->all());
        
        return redirect('/admin/productos')->with('success', 'Producto actualizado correctamente.');
    }

    // 6. Desactivar producto (Borrado lógico) (NUEVO)
    public function destroy($id) {
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => false]); // No lo borramos de la BD, solo lo ocultamos
        
        return redirect('/admin/productos')->with('success', 'Producto desactivado. Ya no aparecerá en las ventas.');
    }
}