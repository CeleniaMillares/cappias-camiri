<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Producto;

class PublicController extends Controller
{
    public function home()
    {
        $productos = Producto::with('socio')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->latest()
            ->take(4)
            ->get();

        return view('welcome', compact('productos'));
    }

    public function cursos() {
        $cursos = Curso::with([
            'instructor',
            'horarios' => fn ($query) => $query
                ->withCount('inscripciones')
                ->orderBy('created_at')
                ->orderBy('id_horario_curso'),
        ])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('publico.cursos', compact('cursos'));
    }

    public function productos() {
        $productos = Producto::with('categoria', 'socio')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->get();
        return view('publico.productos', compact('productos')); // 🔥 publico
    }
}