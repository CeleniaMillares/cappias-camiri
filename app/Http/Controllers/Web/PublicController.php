<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Producto;

class PublicController extends Controller
{
    public function cursos() {
        $cursos = Curso::with('instructor')->where('activo', true)->get();
        return view('publico.cursos', compact('cursos')); // 🔥 publico (no public)
    }

    public function productos() {
        $productos = Producto::with('categoria', 'socio.usuario')
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->get();
        return view('publico.productos', compact('productos')); // 🔥 publico
    }
}