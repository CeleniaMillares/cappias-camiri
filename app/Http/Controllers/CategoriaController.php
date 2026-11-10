<?php
namespace App\Http\Controllers;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index() {
        return response()->json(Categoria::where('activo', true)->get());
    }

    public function store(Request $request) {
        $request->validate(['nombre' => 'required|string|max:100|unique:categorias']);
        $categoria = Categoria::create($request->all());
        return response()->json($categoria, 201);
    }

    public function show($id) {
        return response()->json(Categoria::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $categoria = Categoria::findOrFail($id);
        $categoria->update($request->all());
        return response()->json($categoria);
    }

    public function destroy($id) {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['activo' => false]); // Borrado lógico
        return response()->json(['mensaje' => 'Categoría desactivada']);
    }
}