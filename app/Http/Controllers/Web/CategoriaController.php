<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index() {
        $categorias = Categoria::orderBy('nombre')->paginate(10)->appends(request()->query());
        return view('admin.categorias.index', compact('categorias'));
    }

    public function create() {
        return view('admin.categorias.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u', 'unique:categorias,nombre'],
            'descripcion' => 'nullable|string|max:255'
        ]);

        Categoria::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => true
        ]);

        return redirect('/admin/categorias')->with('success', 'Categoría creada correctamente.');
    }

    public function edit($id) {
        $categoria = Categoria::findOrFail($id);
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, $id) {
        $categoria = Categoria::findOrFail($id);
        
        $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u', 'unique:categorias,nombre,' . $id . ',id_categoria'],
        ]);

        $activa = $request->has('activo');
        DB::transaction(function () use ($categoria, $request, $activa) {
            $categoria->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'activo' => $activa,
            ]);

            if (!$activa) {
                $this->desactivarProductosActivos($categoria);
            }
        });

        return redirect('/admin/categorias')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy($id) {
        DB::transaction(function () use ($id) {
            $categoria = Categoria::findOrFail($id);
            $categoria->update(['activo' => false]);
            $this->desactivarProductosActivos($categoria);
        });

        return redirect('/admin/categorias')->with('success', 'Categoría desactivada.');
    }

    private function desactivarProductosActivos(Categoria $categoria): void
    {
        $categoria->productos()
            ->where('activo', true)
            ->get()
            ->each(fn ($producto) => $producto->update(['activo' => false]));
    }
}