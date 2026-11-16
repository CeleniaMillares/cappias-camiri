<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feria;
use App\Models\Socio;
use App\Support\InsensitiveSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FeriaController extends Controller
{
    public function index(Request $request) {
        $query = Feria::query();

        // Filtro por nombre o lugar
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    InsensitiveSearch::whereContains($q, 'nombre', $request->buscar);
                })->orWhere(function ($q) use ($request) {
                    InsensitiveSearch::whereContains($q, 'lugar', $request->buscar);
                });
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
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
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
        $feria->load('socios');
        $socios = Socio::with('usuario')
            ->get()
            ->sortBy(fn ($socio) => $socio->usuario->nombre ?? '')
            ->values();

        return view('admin.ferias.edit', compact('feria', 'socios'));
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'imagen_url' => 'nullable|image|max:2048',
            'gestionar_socios' => 'sometimes|boolean',
            'socios' => 'sometimes|array',
            'socios.*' => [
                'uuid',
                'distinct',
                Rule::exists('socios', 'id_socio')->whereNull('deleted_at'),
            ],
            'estados' => 'sometimes|array',
            'estados.*' => 'required|in:CONFIRMADO,PENDIENTE',
        ]);

        $feria = Feria::findOrFail($id);
        $data = Arr::only($validated, ['nombre', 'fecha', 'lugar']);

        if ($request->hasFile('imagen_url')) {
            if ($feria->imagen_url) {
                Storage::disk('public')->delete($feria->imagen_url);
            }
            $data['imagen_url'] = $request->file('imagen_url')->store('ferias', 'public');
        }

        DB::transaction(function () use ($request, $feria, $data, $validated) {
            $feria->update($data);

            if ($request->boolean('gestionar_socios')) {
                $participantes = [];
                foreach ($validated['socios'] ?? [] as $idSocio) {
                    $participantes[$idSocio] = [
                        'estado' => $validated['estados'][$idSocio] ?? 'CONFIRMADO',
                    ];
                }

                $feria->socios()->sync($participantes);
            }
        });

        return redirect('/admin/ferias')->with('success', 'Feria actualizada correctamente.');
    }

    public function destroy($id) {
        $feria = Feria::findOrFail($id);
        $feria->delete();
        return redirect('/admin/ferias')->with('success', 'Feria eliminada correctamente.');
    }
}