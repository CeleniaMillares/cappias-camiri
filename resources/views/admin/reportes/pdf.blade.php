<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte CAPPIAS Camiri</title>
    <style>
        body { font-family: Helvetica, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #1e293b; }
        .header p { margin: 5px 0 0; color: #666; font-size: 10px; }
        .logo { max-height: 65px; max-width: 180px; margin-bottom: 6px; }
        .info { margin-bottom: 12px; background: #f8fafc; padding: 8px; }
        .info p { margin: 3px 0; }
        h2 { margin: 16px 0 6px; font-size: 13px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 14px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        th { background: #1e293b; color: white; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f1f5f9; }
        .number { text-align: right; }
        .total-row { background: #dcfce7; font-weight: bold; }
        .empty { text-align: center; color: #666; padding: 12px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('img/logo_cappias.png')))
            <img class="logo" src="{{ public_path('img/logo_cappias.png') }}" alt="Logo CAPPIAS">
        @else
            <h1 class="brand">CAPPIAS Camiri</h1>
        @endif
        <p>Reporte financiero y de operaciones</p>
    </div>

    <div class="info">
        <p><strong>Periodo:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        <p><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <h2>Resumen financiero</h2>
    <table>
        <thead><tr><th>Concepto</th><th class="number">Monto (Bs.)</th></tr></thead>
        <tbody>
            <tr><td>Ventas totales</td><td class="number">{{ number_format($totalVentas, 2) }}</td></tr>
            <tr><td>Comisión de ventas CAPPIAS</td><td class="number">{{ number_format($comisionVentas, 2) }}</td></tr>
            <tr><td>Neto para socios</td><td class="number">{{ number_format($netoSocios, 2) }}</td></tr>
            <tr><td>Inscripciones a cursos</td><td class="number">{{ number_format($totalCursos, 2) }}</td></tr>
            <tr><td>Comisión de cursos CAPPIAS</td><td class="number">{{ number_format($comisionCursos, 2) }}</td></tr>
            <tr><td>Matrículas</td><td class="number">{{ number_format($totalMatriculas, 2) }}</td></tr>
            <tr><td>Mensualidades</td><td class="number">{{ number_format($totalMensualidades, 2) }}</td></tr>
            <tr><td>Total membresías</td><td class="number">{{ number_format($totalMembresias, 2) }}</td></tr>
        </tbody>
        <tfoot>
            <tr class="total-row"><td>INGRESOS TOTALES CAPPIAS</td><td class="number">Bs. {{ number_format($ingresosTotales, 2) }}</td></tr>
        </tfoot>
    </table>

    <h2>Detalle de ventas</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Registrado por</th><th>Origen</th><th class="number">Total (Bs.)</th><th class="number">Comisión (Bs.)</th></tr></thead>
        <tbody>
            @forelse($ventas as $venta)
                <tr>
                    <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->directiva->nombre ?? 'N/A' }}</td>
                    <td>{{ $venta->feria->nombre ?? 'Sede CAPPIAS' }}</td>
                    <td class="number">{{ number_format($venta->total, 2) }}</td>
                    <td class="number">{{ number_format($venta->comision_cappias, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No hay ventas en el periodo.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalle de cursos confirmados</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Alumno</th><th>Curso</th><th class="number">Costo (Bs.)</th><th class="number">Comisión (Bs.)</th></tr></thead>
        <tbody>
            @forelse($inscripciones as $inscripcion)
                <tr>
                    <td>{{ $inscripcion->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $inscripcion->alumno->nombres ?? 'N/A' }}</td>
                    <td>{{ $inscripcion->horarioCurso->curso->nombre ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($inscripcion->horarioCurso->curso->costo ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($inscripcion->comision_cappias, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No hay cursos confirmados en el periodo.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalle de membresías confirmadas</h2>
    <table>
        <thead><tr><th>Fecha de pago</th><th>Socio</th><th>Tipo</th><th>Método</th><th class="number">Monto (Bs.)</th></tr></thead>
        <tbody>
            @forelse($cuotas as $cuota)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }}</td>
                    <td>{{ $cuota->socio->usuario->nombre ?? 'N/A' }}</td>
                    <td>{{ $cuota->tipo }}</td>
                    <td>{{ $cuota->metodo }}</td>
                    <td class="number">{{ number_format($cuota->monto, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No hay membresías confirmadas en el periodo.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado por el Sistema de Gestión CAPPIAS Camiri.</p>
        <p>Este reporte es confidencial y de uso interno.</p>
    </div>
</body>
</html>
