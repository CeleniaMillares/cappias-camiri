<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - CAPPIAS</title>
    <style>
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 12px; 
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 20px; 
            color: #1e293b;
        }
        .header p { 
            margin: 5px 0 0 0; 
            color: #666; 
            font-size: 11px;
        }
        .info { 
            margin-bottom: 15px; 
            background: #f8fafc; 
            padding: 10px; 
            border-radius: 4px;
        }
        .info p { margin: 3px 0; }
        .info strong { color: #1e293b; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th, td { 
            border: 1px solid #cbd5e1; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background: #1e293b; 
            color: white;
            font-size: 11px;
            text-transform: uppercase;
        }
        tbody tr:nth-child(even) { background: #f1f5f9; }
        
        .total-row {
            background: #dcfce7 !important;
            font-weight: bold;
            font-size: 13px;
        }
        
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #999;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <h1>🌿 CAPPIAS Camiri</h1>
        <p>Reporte de Ventas Generado Automáticamente</p>
    </div>

    <!-- Información del reporte -->
    <div class="info">
        <p><strong>Periodo del Reporte:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        <p><strong>Fecha de Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><strong>Total de Ventas:</strong> {{ count($ventas) }} transacciones</p>
        <p><strong>Ingresos Totales:</strong> Bs. {{ number_format($totalVentas, 2) }}</p>
    </div>

    <!-- Tabla de Ventas -->
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Fecha y Hora</th>
                <th style="width: 35%;">Registrado por</th>
                <th style="width: 20%;">Origen</th>
                <th style="width: 20%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $venta->directiva->nombre ?? 'N/A' }}</td>
                <td>{{ $venta->feria ? $venta->feria->nombre : 'Sede CAPPIAS' }}</td>
                <td style="text-align: right;">Bs. {{ number_format($venta->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">
                    No hay ventas registradas en este periodo.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">TOTAL GENERAL:</td>
                <td style="text-align: right;">Bs. {{ number_format($totalVentas, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Documento generado por el Sistema de Gestión CAPPIAS Camiri</p>
        <p>Este reporte es confidencial y de uso interno.</p>
    </div>

</body>
</html>