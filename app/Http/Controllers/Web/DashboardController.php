<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Inscripcion;
use App\Models\Producto;
use App\Models\CuotaSocio;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // 1. SI ES ADMINISTRADOR (Solo ve métricas del sistema)
        if ($user->hasRole('Administrador')) {
            $totalUsuarios = \App\Models\Usuario::count();
            $totalAuditorias = \App\Models\Auditoria::count();
            $ultimasAcciones = \App\Models\Auditoria::with('usuario')->orderBy('created_at', 'desc')->take(5)->get();
            
            return view('admin.dashboard_admin', compact('totalUsuarios', 'totalAuditorias', 'ultimasAcciones'));
        }

        // 2. SI ES SOCIO/INSTRUCTOR
        if ($user->hasRole('Socio/Instructor')) {
            $productos = $user->productosComoSocio;
            $ventas = \App\Models\DetalleVenta::whereHas('producto', function($q) use ($user) {
                $q->where('id_usuario_socio', $user->id_usuario);
            })->with('venta')->get();
            return view('admin.dashboard_socio', compact('productos', 'ventas'));
        }

        // 3. SI ES DIRECTIVA (El Dashboard general con gráficos)
        $estadosVentaPagada = ['PAGADA', 'LIQUIDADA'];
        $ventasHoy = Venta::whereDate('created_at', today())
            ->whereIn('estado', $estadosVentaPagada)
            ->sum('total');
        $totalAlumnos = Inscripcion::distinct('id_alumno')->count('id_alumno');
        $productosBajoStock = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->where('activo', true)->get();
        $ultimasVentas = Venta::with('directiva')
            ->whereIn('estado', $estadosVentaPagada)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $comisionVentas = Venta::whereIn('estado', $estadosVentaPagada)->sum('comision_cappias');
        $comisionCursos = Inscripcion::where('estado', 'CONFIRMADA')->sum('comision_cappias');
        $totalMembresias = CuotaSocio::where('estado', 'CONFIRMADO')->sum('monto');

        // 🔥 Lógica para los gráficos (Últimos 7 días)
        $ventasSemana = [];
        $fechasSemana = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $fechasSemana[] = $fecha->translatedFormat('D'); // Lun, Mar, Mié...
            $ventasSemana[] = Venta::whereDate('created_at', $fecha->toDateString())
                ->whereIn('estado', $estadosVentaPagada)
                ->sum('total');
        }

        return view('admin.dashboard', compact(
            'ventasHoy',
            'totalAlumnos',
            'productosBajoStock',
            'ultimasVentas',
            'ventasSemana',
            'fechasSemana',
            'comisionVentas',
            'comisionCursos',
            'totalMembresias'
        ));
    }
}