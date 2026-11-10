@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reportes y Estadísticas</h1>
        <p class="text-gray-500 text-sm mt-1">Análisis financiero de CAPPIAS Camiri.</p>
    </div>
</div>

<!-- Filtro de Fechas -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-8">
    <form method="GET" action="/admin/reportes" class="flex flex-wrap gap-4 items-end justify-between">
        <div class="flex gap-4 items-end w-full md:w-auto">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Desde:</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="border-gray-300 rounded-lg p-2 border text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Hasta:</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="border-gray-300 rounded-lg p-2 border text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 font-semibold shadow-sm h-[42px]">
                <i class="fas fa-filter mr-2"></i> Filtrar
            </button>
        </div>
        <div class="flex gap-2">
            <a href="/admin/reportes/pdf?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}" 
            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-semibold shadow-sm h-[42px] flex items-center">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
            <a href="/admin/reportes/excel?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-semibold shadow-sm h-[42px] flex items-center">
                <i class="fas fa-file-excel mr-2"></i> Excel
            </a>
        </div>
    </form>
</div>

<!-- RESUMEN GLOBAL -->
<div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl shadow-lg p-6 mb-8 text-white flex justify-between items-center">
    <div>
        <p class="text-slate-300 uppercase tracking-wider font-bold text-sm">Ingresos Totales de CAPPIAS en el Periodo</p>
        <p class="text-4xl font-extrabold mt-1">Bs. {{ number_format($ingresosTotales, 2) }}</p>
    </div>
    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
        <i class="fas fa-chart-line text-3xl text-green-400"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- TARJETA: VENTAS DE PRODUCTOS -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="font-bold text-gray-800 text-lg"><i class="fas fa-shopping-cart text-blue-500 mr-2"></i> Ventas</h2>
            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">{{ $porcentajeVenta }}% Comisión</span>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Total Vendido:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($totalVentas, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Comisión CAPPIAS:</span>
                <span class="font-bold text-blue-600">Bs. {{ number_format($comisionVentas, 2) }}</span>
            </div>
            <div class="flex justify-between border-t pt-2 mt-2">
                <span class="text-gray-600 font-semibold">Neto para Socios:</span>
                <span class="font-bold text-green-600">Bs. {{ number_format($netoSocios, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- TARJETA: CURSOS -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="font-bold text-gray-800 text-lg"><i class="fas fa-chalkboard-teacher text-indigo-500 mr-2"></i> Cursos</h2>
            <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-bold">{{ $porcentajeCurso }}% Comisión</span>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Total Inscripciones:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($totalCursos, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Comisión CAPPIAS:</span>
                <span class="font-bold text-indigo-600">Bs. {{ number_format($comisionCursos, 2) }}</span>
            </div>
            <div class="flex justify-between border-t pt-2 mt-2">
                <span class="text-gray-600 font-semibold">Neto Instructores:</span>
                <span class="font-bold text-green-600">Bs. {{ number_format($totalCursos - $comisionCursos, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- TARJETA: MEMBRESÍAS -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="font-bold text-gray-800 text-lg"><i class="fas fa-id-card text-yellow-500 mr-2"></i> Socios</h2>
            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Membresías</span>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Matrículas:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($totalMatriculas, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Mensualidades:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($totalMensualidades, 2) }}</span>
            </div>
            <div class="flex justify-between border-t pt-2 mt-2">
                <span class="text-gray-600 font-semibold">Total Ingresos:</span>
                <span class="font-bold text-green-600">Bs. {{ number_format($totalMembresias, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICO DE TORTA -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-center justify-around mb-8">
    <div class="w-full md:w-1/3 mb-6 md:mb-0 text-center md:text-left">
        <h2 class="text-lg font-bold text-gray-800 mb-2 border-b pb-2"><i class="fas fa-chart-pie text-green-500 mr-2"></i> Origen de Ingresos</h2>
        <p class="text-sm text-gray-500">Distribución de las comisiones e ingresos generados por CAPPIAS.</p>
    </div>
    <div class="w-full md:w-1/2 h-64">
        <canvas id="graficoIngresos"></canvas>
    </div>
</div>

<!-- Script de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxIngresos = document.getElementById('graficoIngresos').getContext('2d');
    const graficoIngresos = new Chart(ctxIngresos, {
        type: 'doughnut',
        data: {
            labels: ['Comisión Ventas', 'Comisión Cursos', 'Membresías (Socios)'],
            datasets: [{
                data: [
                    {{ $comisionVentas }}, 
                    {{ $comisionCursos }}, 
                    {{ $totalMembresias }}
                ],
                backgroundColor: [
                    '#3b82f6', // Azul (Ventas)
                    '#6366f1', // Indigo (Cursos)
                    '#f59e0b'  // Amarillo (Membresías)
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endsection