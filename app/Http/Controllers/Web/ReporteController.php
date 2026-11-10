<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Inscripcion;
use App\Models\CuotaSocio;
use App\Models\Parametro;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index(Request $request) {
        // Fechas por defecto (Mes actual)
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        // ==========================================
        // 1. REPORTE DE VENTAS
        // ==========================================
        $ventas = Venta::with(['directiva', 'detalles.producto'])
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->whereIn('estado', ['PAGADA', 'LIQUIDADA'])
            ->get();

        $totalVentas = $ventas->sum('total');
        $porcentajeVenta = Parametro::where('clave', 'comision_venta')->value('valor') ?? 5;
        $comisionVentas = $ventas->sum('comision_cappias');
        $netoSocios = $totalVentas - $comisionVentas;

        // ==========================================
        // 2. REPORTE DE CURSOS
        // ==========================================
        $inscripciones = Inscripcion::with(['horarioCurso.curso'])
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('estado', 'CONFIRMADA')
            ->get();

        $totalCursos = 0;
        foreach($inscripciones as $ins) {
            $totalCursos += $ins->horarioCurso->curso->costo ?? 0;
        }
        $porcentajeCurso = Parametro::where('clave', 'comision_curso')->value('valor') ?? 20;
        $comisionCursos = $inscripciones->sum('comision_cappias');

        // ==========================================
        // 3. REPORTE DE SOCIOS (MATRÍCULAS Y MENSUALIDADES)
        // ==========================================
        $cuotas = CuotaSocio::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->where('estado', 'CONFIRMADO')
            ->get();

        $totalMatriculas = $cuotas->where('tipo', 'MATRICULA')->sum('monto');
        $totalMensualidades = $cuotas->where('tipo', 'MENSUALIDAD')->sum('monto');
        $totalMembresias = $totalMatriculas + $totalMensualidades;

        // ==========================================
        // TOTALES GLOBALES
        // ==========================================
        $ingresosTotales = $comisionVentas + $comisionCursos + $totalMembresias;

        return view('admin.reportes.index', compact(
            'fechaInicio', 'fechaFin',
            'totalVentas', 'comisionVentas', 'netoSocios', 'porcentajeVenta',
            'totalCursos', 'comisionCursos', 'porcentajeCurso',
            'totalMatriculas', 'totalMensualidades', 'totalMembresias',
            'ingresosTotales'
        ));
    }

    // 🔥 MÉTODO PARA GENERAR PDF
    public function exportPdf(Request $request) {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $ventas = Venta::with('directiva')
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->whereIn('estado', ['PAGADA', 'LIQUIDADA'])
            ->get();
            
        $totalVentas = $ventas->sum('total');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reportes.pdf', compact('ventas', 'fechaInicio', 'fechaFin', 'totalVentas'));
        
        return $pdf->download('reporte_ventas_' . $fechaInicio . '.pdf');
    }

    // 🔥 MÉTODO PARA GENERAR EXCEL (CSV)
    public function exportCsv(Request $request) {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $ventas = Venta::with('directiva')
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->whereIn('estado', ['PAGADA', 'LIQUIDADA'])
            ->get();

        $filename = "reporte_ventas_{$fechaInicio}_al_{$fechaFin}.csv";
        
        // Cabeceras para forzar la descarga en el navegador
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Abrimos el flujo de salida
        $handle = fopen('php://output', 'w');
        
        // Agregamos BOM para que Excel reconozca los acentos correctamente
        fputs($handle, "\xEF\xBB\xBF");
        
        // Encabezados del CSV
        fputcsv($handle, ['Fecha', 'Registrado por', 'Origen', 'Total (Bs.)']);
        
        // Datos
        foreach ($ventas as $venta) {
            fputcsv($handle, [
                $venta->created_at->format('d/m/Y H:i'),
                $venta->directiva->nombre ?? 'N/A',
                $venta->feria ? $venta->feria->nombre : 'Sede CAPPIAS',
                $venta->total
            ]);
        }
        
        // Fila de Total
        fputcsv($handle, ['', '', 'TOTAL:', $ventas->sum('total')]);
        
        fclose($handle);
        exit;
    }
}