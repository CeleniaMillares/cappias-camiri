<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index() {
        $categorias = Categoria::all();
        return view('admin.categorias.index', compact('categorias'));
    }

    public function create() {
        return view('admin.categorias.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
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
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $id . ',id_categoria',
        ]);

        // Actualizamos los campos y verificamos si el checkbox está marcado
        $categoria->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false, // 🔥 Si está marcado es true, si no, false
        ]);

        return redirect('/admin/categorias')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy($id) {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['activo' => false]); 
        return redirect('/admin/categorias')->with('success', 'Categoría desactivada.');
    }
}