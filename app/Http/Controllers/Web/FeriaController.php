<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeriaController extends Controller
{
    public function index(Request $request) {
        $query = Feria::query();

        // Filtro por nombre o lugar
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'LIKE', '%' . $request->buscar . '%')
                  ->orWhere('lugar', 'LIKE', '%' . $request->buscar . '%');
            });
        }

        // Filtro por fecha exacta
        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        $ferias = $query->orderBy('fecha', 'desc')->get();

        return view('admin.ferias.index', compact('ferias'));
    }

    public function create() {
        return view('admin.ferias.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'imagen_url' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen_url')) {
            $data['imagen_url'] = $request->file('imagen_url')->store('ferias', 'public');
        }

        Feria::create($data);
        return redirect('/admin/ferias')->with('success', 'Feria registrada correctamente.');
    }

    public function edit($id) {
        $feria = Feria::findOrFail($id);
        return view('admin.ferias.edit', compact('feria'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'imagen_url' => 'nullable|image|max:2048',
        ]);

        $feria = Feria::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('imagen_url')) {
            if ($feria->imagen_url) {
                Storage::disk('public')->delete($feria->imagen_url);
            }
            $data['imagen_url'] = $request->file('imagen_url')->store('ferias', 'public');
        }

        $feria->update($data);
        return redirect('/admin/ferias')->with('success', 'Feria actualizada correctamente.');
    }

    public function destroy($id) {
        $feria = Feria::findOrFail($id);
        $feria->delete();
        return redirect('/admin/ferias')->with('success', 'Feria eliminada correctamente.');
    }
}