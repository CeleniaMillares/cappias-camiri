@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Panel de Control</h1>

<!-- Tarjetas de Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500">
        <h3 class="text-gray-500 text-sm font-semibold">Ventas de Hoy</h3>
        <p class="text-3xl font-bold text-blue-600">Bs. {{ number_format($ventasHoy, 2) }}</p>
    </div>
    
    <div class="bg-white p-6 rounded shadow border-l-4 border-green-500">
        <h3 class="text-gray-500 text-sm font-semibold">Alumnos Inscritos</h3>
        <p class="text-3xl font-bold text-green-600">{{ $totalAlumnos }}</p>
    </div>

    <div class="bg-white p-6 rounded shadow border-l-4 border-red-500">
        <h3 class="text-gray-500 text-sm font-semibold">Alertas de Stock Bajo</h3>
        <p class="text-3xl font-bold text-red-600">{{ $productosBajoStock->count() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Alertas de Stock -->
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-lg font-bold mb-4 border-b pb-2">Productos por Agotarse</h2>
        @if($productosBajoStock->isEmpty())
            <p class="text-gray-500 text-sm">Todo el inventario está en niveles óptimos.</p>
        @else
            <ul class="space-y-3">
                @foreach($productosBajoStock as $prod)
                <li class="flex justify-between items-center bg-red-50 p-2 rounded">
                    <span class="font-semibold">{{ $prod->nombre }}</span>
                    <span class="text-red-600 text-sm font-bold">Quedan {{ $prod->stock_actual }} (Mínimo: {{ $prod->stock_minimo }})</span>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Últimas Ventas -->
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-lg font-bold mb-4 border-b pb-2">Últimas Ventas</h2>
        <table class="w-full text-left text-sm">
            <tbody>
                @foreach($ultimasVentas as $venta)
                <tr class="border-b">
                    <td class="py-2">{{ $venta->created_at->format('H:i') }}</td>
                    <td class="py-2">{{ $venta->directiva->nombre }}</td>
                    <td class="py-2 text-right font-bold text-green-600">Bs. {{ number_format($venta->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection