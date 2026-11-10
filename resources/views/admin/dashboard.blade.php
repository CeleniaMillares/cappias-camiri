@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-gray-800">Panel de Control</h1>
    <p class="text-gray-500 mt-1">Resumen general del estado de CAPPIAS Camiri hoy.</p>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Ventas de Hoy -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500 flex items-center justify-between">
        <div>
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wide">Ventas de Hoy</h3>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">Bs. {{ number_format($ventasHoy, 2) }}</p>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-xl">
            <i class="fas fa-cash-register"></i>
        </div>
    </div>
    
    <!-- Alumnos Inscritos -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-green-500 flex items-center justify-between">
        <div>
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wide">Alumnos Inscritos</h3>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $totalAlumnos }}</p>
        </div>
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center text-xl">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Alertas de Stock -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-red-500 flex items-center justify-between">
        <div>
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wide">Alertas de Stock Bajo</h3>
            <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $productosBajoStock->count() }}</p>
        </div>
        <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-xl">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
    </div>
</div>

<!-- Gráfico de Ventas -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-chart-area text-blue-500 mr-2"></i> Ventas de los Últimos 7 Días</h2>
    <canvas id="graficoVentas" height="80"></canvas>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Alertas de Stock (Regla de Negocio) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-boxes text-red-500"></i> Productos por Agotarse
        </h2>
        @if($productosBajoStock->isEmpty())
            <div class="bg-green-50 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <p>Todo el inventario está en niveles óptimos.</p>
            </div>
        @else
            <ul class="space-y-3">
                @foreach($productosBajoStock as $prod)
                <li class="flex justify-between items-center bg-red-50 border border-red-100 p-3 rounded-lg">
                    <div>
                        <p class="font-bold text-gray-800">{{ $prod->nombre }}</p>
                        <p class="text-xs text-gray-500">Categoría: {{ $prod->categoria->nombre ?? 'N/A' }}</p>
                    </div>
                    <span class="bg-white text-red-600 px-3 py-1 rounded-full text-sm font-bold shadow-sm border border-red-200">
                        Quedan: {{ $prod->stock_actual }} / Mín: {{ $prod->stock_minimo }}
                    </span>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Últimas Ventas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-history text-blue-500"></i> Últimas Ventas
        </h2>
        @if($ultimasVentas->isEmpty())
            <p class="text-gray-500 text-center py-4">Aún no hay ventas registradas hoy.</p>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="py-2 px-3 rounded-l-lg">Hora</th>
                        <th class="py-2 px-3">Registrado por</th>
                        <th class="py-2 px-3 rounded-r-lg text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimasVentas as $venta)
                    <tr class="border-b border-gray-50 last:border-0">
                        <td class="py-3 px-3 text-gray-600">{{ $venta->created_at->format('H:i') }}</td>
                        <td class="py-3 px-3 font-medium">{{ $venta->directiva->nombre ?? 'Usuario' }}</td>
                        <td class="py-3 px-3 text-right font-bold text-green-600">Bs. {{ number_format($venta->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<!-- 🔥 Script de Chart.js para el Gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    const graficoVentas = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($fechasSemana) !!},
            datasets: [{
                label: 'Ventas (Bs.)',
                data: {!! json_encode($ventasSemana) !!},
                borderColor: '#3b82f6', // Azul
                backgroundColor: 'rgba(59, 130, 246, 0.2)', // Azul transparente
                borderWidth: 3,
                tension: 0.4, // Curva suave
                fill: true,
                pointBackgroundColor: '#1d4ed8',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection