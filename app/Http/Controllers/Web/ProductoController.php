<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Usuario;
use App\Support\InsensitiveSearch;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            InsensitiveSearch::whereContains($query, 'nombre', $request->buscar);
        }

        // Filtro por Categoría
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        // Filtro por Socio
        if ($request->filled('id_usuario_socio')) {
            $query->where('id_usuario_socio', $request->id_usuario_socio);
        }

        $productos = $query->paginate(10)->appends($request->query());

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
        $data = $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_usuario_socio' => 'required|exists:usuarios,id_usuario',
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'materiales' => 'nullable|string|max:255',
            'tecnica' => 'nullable|string|max:255',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
            'fotografia_url' => 'nullable|image|max:2048',
        ]);

        if ($response = $this->validarCategoriaYSocio($request)) {
            return $response;
        }

        if ($request->hasFile('fotografia_url')) {
            $data['fotografia_url'] = $request->file('fotografia_url')->store('productos', 'public');
        }
        $data['activo'] = true;

        Producto::create($data);

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
        
        $data = $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_usuario_socio' => 'required|exists:usuarios,id_usuario',
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'materiales' => 'nullable|string|max:255',
            'tecnica' => 'nullable|string|max:255',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
            'fotografia_url' => 'nullable|image|max:2048',
        ]);

        if ($response = $this->validarCategoriaYSocio($request)) {
            return $response;
        }

        if ($request->hasFile('fotografia_url')) {
            $imagenAnterior = $producto->fotografia_url;
            $data['fotografia_url'] = $request->file('fotografia_url')->store('productos', 'public');
        }

        $producto->update($data);

        if (isset($imagenAnterior)) {
            Storage::disk('public')->delete($imagenAnterior);
        }
        
        return redirect('/admin/productos')->with('success', 'Producto actualizado correctamente.');
    }

    // 6. Desactivar producto (Borrado lógico) (NUEVO)
    public function destroy($id) {
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => false]); // No lo borramos de la BD, solo lo ocultamos
        
        return redirect('/admin/productos')->with('success', 'Producto desactivado. Ya no aparecerá en las ventas.');
    }

    public function addStock(Request $request, $id): RedirectResponse
    {
        $data = $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data, $id) {
            $producto = Producto::whereKey($id)->lockForUpdate()->firstOrFail();
            $producto->stock_actual = (int) $producto->stock_actual + (int) $data['cantidad'];
            $producto->save();
        });

        return back()->with('success', 'Stock actualizado correctamente.');
    }

    private function validarCategoriaYSocio(Request $request): ?RedirectResponse
    {
        $categoria = Categoria::findOrFail($request->id_categoria);
        if (!$categoria->activo) {
            return back()
                ->withErrors(['id_categoria' => 'La categoría seleccionada está inactiva.'])
                ->withInput();
        }

        $socio = Usuario::findOrFail($request->id_usuario_socio);
        if (!$socio->hasRole('Socio/Instructor')) {
            return back()
                ->withErrors(['id_usuario_socio' => 'El usuario seleccionado no es un socio válido.'])
                ->withInput();
        }

        return null;
    }
}