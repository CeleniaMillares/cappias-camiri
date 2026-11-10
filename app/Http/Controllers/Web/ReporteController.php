<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CuotaSocio;
use App\Models\Inscripcion;
use App\Models\Parametro;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        [$fechaInicio, $fechaFin] = $this->validarFechas($request);
        $datos = $this->calcularDatosReporte($fechaInicio, $fechaFin);

        return view('admin.reportes.index', $datos);
    }

    public function exportPdf(Request $request)
    {
        [$fechaInicio, $fechaFin] = $this->validarFechas($request);
        $datos = $this->calcularDatosReporte($fechaInicio, $fechaFin);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reportes.pdf', $datos);

        return $pdf->download("reporte_cappias_{$fechaInicio}_al_{$fechaFin}.pdf");
    }

    public function exportCsv(Request $request)
    {
        [$fechaInicio, $fechaFin] = $this->validarFechas($request);
        $datos = $this->calcularDatosReporte($fechaInicio, $fechaFin);
        $filename = "reporte_cappias_{$fechaInicio}_al_{$fechaFin}.csv";

        return response()->stream(function () use ($datos) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new \RuntimeException('No se pudo abrir la salida del reporte CSV.');
            }

            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['RESUMEN FINANCIERO']);
            fputcsv($handle, ['Concepto', 'Monto (Bs.)']);
            fputcsv($handle, ['Ventas totales', $datos['totalVentas']]);
            fputcsv($handle, ['Comisión de ventas CAPPIAS', $datos['comisionVentas']]);
            fputcsv($handle, ['Neto para socios', $datos['netoSocios']]);
            fputcsv($handle, ['Inscripciones a cursos', $datos['totalCursos']]);
            fputcsv($handle, ['Comisión de cursos CAPPIAS', $datos['comisionCursos']]);
            fputcsv($handle, ['Matrículas', $datos['totalMatriculas']]);
            fputcsv($handle, ['Mensualidades', $datos['totalMensualidades']]);
            fputcsv($handle, ['Total membresías', $datos['totalMembresias']]);
            fputcsv($handle, ['INGRESOS TOTALES CAPPIAS', $datos['ingresosTotales']]);

            fputcsv($handle, []);
            fputcsv($handle, ['DETALLE DE VENTAS']);
            fputcsv($handle, ['Fecha', 'Registrado por', 'Origen', 'Total (Bs.)', 'Comisión CAPPIAS (Bs.)']);
            foreach ($datos['ventas'] as $venta) {
                fputcsv($handle, [
                    $venta->created_at->format('d/m/Y H:i'),
                    $venta->directiva->nombre ?? 'N/A',
                    $venta->feria->nombre ?? 'Sede CAPPIAS',
                    $venta->total,
                    $venta->comision_cappias,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['DETALLE DE CURSOS']);
            fputcsv($handle, ['Fecha', 'Alumno', 'Curso', 'Costo (Bs.)', 'Comisión CAPPIAS (Bs.)']);
            foreach ($datos['inscripciones'] as $inscripcion) {
                fputcsv($handle, [
                    $inscripcion->created_at->format('d/m/Y H:i'),
                    $inscripcion->alumno->nombres ?? 'N/A',
                    $inscripcion->horarioCurso->curso->nombre ?? 'N/A',
                    $inscripcion->horarioCurso->curso->costo ?? 0,
                    $inscripcion->comision_cappias,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['DETALLE DE MEMBRESÍAS']);
            fputcsv($handle, ['Fecha de pago', 'Socio', 'Tipo', 'Método', 'Monto (Bs.)']);
            foreach ($datos['cuotas'] as $cuota) {
                fputcsv($handle, [
                    $cuota->fecha_pago,
                    $cuota->socio->usuario->nombre ?? 'N/A',
                    $cuota->tipo,
                    $cuota->metodo,
                    $cuota->monto,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function validarFechas(Request $request): array
    {
        $request->merge([
            'fecha_inicio' => $request->input('fecha_inicio', now()->startOfMonth()->toDateString()),
            'fecha_fin' => $request->input('fecha_fin', now()->toDateString()),
        ]);

        $fechas = $request->validate([
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha de fin.',
        ]);

        return [$fechas['fecha_inicio'], $fechas['fecha_fin']];
    }

    private function calcularDatosReporte(string $fechaInicio, string $fechaFin): array
    {
        if ($fechaInicio > $fechaFin) {
            throw ValidationException::withMessages([
                'fecha_inicio' => 'La fecha de inicio no puede ser posterior a la fecha de fin.',
            ]);
        }

        $ventas = Venta::with(['directiva', 'feria', 'detalles.producto'])
            ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->whereIn('estado', ['PAGADA', 'LIQUIDADA'])
            ->get();

        $inscripciones = Inscripcion::with(['alumno', 'horarioCurso.curso'])
            ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->where('estado', 'CONFIRMADA')
            ->get();

        $cuotas = CuotaSocio::with('socio.usuario')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->where('estado', 'CONFIRMADO')
            ->get();

        $totalVentas = (float) $ventas->sum('total');
        $comisionVentas = (float) $ventas->sum('comision_cappias');
        $totalCursos = (float) $inscripciones->sum(
            fn ($inscripcion) => $inscripcion->horarioCurso->curso->costo ?? 0
        );
        $comisionCursos = (float) $inscripciones->sum('comision_cappias');
        $totalMatriculas = (float) $cuotas->where('tipo', 'MATRICULA')->sum('monto');
        $totalMensualidades = (float) $cuotas->where('tipo', 'MENSUALIDAD')->sum('monto');
        $totalMembresias = $totalMatriculas + $totalMensualidades;

        return [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'ventas' => $ventas,
            'inscripciones' => $inscripciones,
            'cuotas' => $cuotas,
            'totalVentas' => $totalVentas,
            'comisionVentas' => $comisionVentas,
            'netoSocios' => $totalVentas - $comisionVentas,
            'porcentajeVenta' => Parametro::where('clave', 'comision_venta')->value('valor') ?? 5,
            'totalCursos' => $totalCursos,
            'comisionCursos' => $comisionCursos,
            'porcentajeCurso' => Parametro::where('clave', 'comision_curso')->value('valor') ?? 20,
            'totalMatriculas' => $totalMatriculas,
            'totalMensualidades' => $totalMensualidades,
            'totalMembresias' => $totalMembresias,
            'ingresosTotales' => $comisionVentas + $comisionCursos + $totalMembresias,
        ];
    }
}
