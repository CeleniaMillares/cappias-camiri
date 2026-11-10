@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Pagos a Socios</h1>
    <p class="text-gray-500 text-sm mt-1">Administra los saldos pendientes y revisa el historial de pagos realizados.</p>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif

<!-- 🔍 BARRA DE FILTROS (Socio, Fecha Inicio, Fecha Fin) -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" action="/admin/liquidaciones" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Buscar por Socio:</label>
            <input type="text" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Ej: María, Pedro..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Desde:</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Hasta:</label>
            <input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="md:col-span-4 flex gap-2 justify-end mt-2">
            <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 transition font-semibold shadow-sm h-[42px]">
                <i class="fas fa-search mr-2"></i> Filtrar
            </button>
            <a href="/admin/liquidaciones" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition font-semibold shadow-sm h-[42px] flex items-center justify-center">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <!-- ============================================= -->
    <!-- 🔴 SECCIÓN 1: SALDOS PENDIENTES               -->
    <!-- ============================================= -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-red-50 flex justify-between items-center">
            <div>
                <h2 class="font-bold text-gray-800"><i class="fas fa-hourglass-half text-red-500 mr-2"></i> Saldos Pendientes de Pago</h2>
                <p class="text-xs text-gray-500 mt-1">Deuda total acumulada por cada socio.</p>
            </div>
            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">{{ count($saldos) }} Socios</span>
        </div>
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-4">Socio</th>
                    <th class="p-4 text-right">Saldo</th>
                    <th class="p-4 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($saldos as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-sm">
                                {{ substr($item['socio']->nombre, 0, 1) }}
                            </div>
                            <span class="font-bold text-gray-800">{{ $item['socio']->nombre }}</span>
                        </div>
                    </td>
                    <td class="p-4 text-right font-bold text-red-600">
                        Bs. {{ number_format($item['saldo'], 2) }}
                    </td>
                    <td class="p-4 text-center">
                        <a href="/admin/liquidaciones/crear/{{ $item['socio']->id_usuario }}" class="inline-block bg-green-50 text-green-700 border border-green-200 px-3 py-2 rounded-lg hover:bg-green-600 hover:text-white transition font-semibold shadow-sm text-xs">
                            <i class="fas fa-hand-holding-usd mr-1"></i> Pagar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-8 text-center text-gray-500">
                        <i class="fas fa-check-circle text-3xl text-green-300 mb-2 block"></i>
                        No hay socios con saldo pendiente.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ============================================= -->
    <!-- 🟢 SECCIÓN 2: HISTORIAL DE PAGOS REALIZADOS   -->
    <!-- ============================================= -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-green-50 flex justify-between items-center">
            <div>
                <h2 class="font-bold text-gray-800"><i class="fas fa-history text-green-600 mr-2"></i> Historial de Pagos Realizados</h2>
                <p class="text-xs text-gray-500 mt-1">Abonos entregados (filtrados por las fechas seleccionadas).</p>
            </div>
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">{{ $historial->count() }} Pagos</span>
        </div>
        <div class="max-h-[600px] overflow-y-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-semibold sticky top-0">
                    <tr>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Socio</th>
                        <th class="p-4">Monto Pagado</th>
                        <th class="p-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($historial as $pago)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 text-xs text-gray-500">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center font-bold text-xs">
                                    {{ substr($pago->socio->nombre ?? 'N', 0, 1) }}
                                </div>
                                <span class="font-semibold text-gray-800 text-xs">{{ $pago->socio->nombre ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="p-4 font-bold text-green-600">
                            Bs. {{ number_format($pago->monto_neto, 2) }}
                        </td>
                        <td class="p-4 text-center">
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Pagado</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <i class="fas fa-receipt text-3xl text-gray-300 mb-2 block"></i>
                            No hay pagos registrados en este periodo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection