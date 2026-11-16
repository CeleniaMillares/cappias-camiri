@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Historial de Ventas</h1>
        <p class="text-gray-500 text-sm mt-1">Administra y filtra las transacciones realizadas.</p>
    </div>
    
    @if(Auth::user()->hasRole('Directiva'))
    <a href="/admin/ventas/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2 font-semibold">
        <i class="fas fa-plus"></i> Nueva Venta
    </a>
    @endif
</div>

<!-- Barra de filtros -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" action="/admin/ventas" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Desde:</label>
            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Hasta:</label>
            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Estado:</label>
            <select name="estado" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border text-sm">
                <option value="">Todos</option>
                <option value="PAGADA" {{ request('estado') == 'PAGADA' ? 'selected' : '' }}>Pagada</option>
                <option value="PENDIENTE_PAGO" {{ request('estado') == 'PENDIENTE_PAGO' ? 'selected' : '' }}>Pendiente</option>
                <option value="LIQUIDADA" {{ request('estado') == 'LIQUIDADA' ? 'selected' : '' }}>Liquidada</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Origen:</label>
            <select name="origen" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border text-sm">
                <option value="">Todos</option>
                <option value="sede" {{ request('origen') == 'sede' ? 'selected' : '' }}>Sede CAPPIAS</option>
                <option value="feria" {{ request('origen') == 'feria' ? 'selected' : '' }}>Feria</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Producto:</label>
            <input type="text" name="producto" value="{{ request('producto') }}" placeholder="Buscar producto..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 border text-sm">
        </div>
        <div class="md:col-span-5 flex gap-2 justify-end mt-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500">
                <i class="fas fa-search mr-2"></i> Filtrar
            </button>
            <a href="/admin/ventas" class="inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600">
                <i class="fas fa-times mr-2" aria-hidden="true"></i> Limpiar Filtros
            </a>
        </div>
    </form>
</div>

<!-- Resumen de filtros aplicados -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
        <p class="text-xs text-gray-500 uppercase font-bold">Total Cobrado (Pagadas + Liquidadas)</p>
        <p class="text-2xl font-extrabold text-green-600">Bs. {{ number_format($totalFiltrado, 2) }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
        <p class="text-xs text-gray-500 uppercase font-bold">Total Pendiente de Pago</p>
        <p class="text-2xl font-extrabold text-yellow-600">Bs. {{ number_format($totalPendiente, 2) }}</p>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Fecha</th>
                <th class="p-4">Origen</th>
                <th class="p-4">Registrado por</th>
                <th class="p-4">Productos</th>
                <th class="p-4">Total</th>
                <th class="p-4">Estado</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($ventas as $venta)
            <tr class="hover:bg-gray-50 transition">
                <td class="p-4 text-gray-800">{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                <td class="p-4">
                    @if($venta->feria)
                        <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-bold"><i class="fas fa-store mr-1"></i> {{ $venta->feria->nombre }}</span>
                    @else
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold"><i class="fas fa-building mr-1"></i> Sede</span>
                    @endif
                </td>
                <td class="p-4">{{ $venta->directiva->nombre ?? 'N/A' }}</td>
                <td class="p-4">
                    <ul class="list-disc list-inside text-xs">
                        @foreach($venta->detalles as $det)
                            <li>{{ $det->producto->nombre }} (x{{ $det->cantidad }})</li>
                        @endforeach
                    </ul>
                </td>
                <td class="p-4 font-bold text-gray-800">Bs. {{ number_format($venta->total, 2) }}</td>
                <td class="p-4">
                    @if($venta->estado === 'PAGADA')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Pagada</span>
                    @elseif($venta->estado === 'PENDIENTE_PAGO')
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Pendiente</span>
                    @elseif($venta->estado === 'LIQUIDADA')
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">Liquidada</span>
                    @else
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">{{ $venta->estado }}</span>
                    @endif
                </td>
                <td class="p-4">
                    @if(Auth::user()->hasRole('Directiva'))
                        @foreach($venta->pagos->where('estado', 'PENDIENTE') as $pago)
                            @if($pago->comprobante_url)
                                <a href="{{ asset('storage/' . $pago->comprobante_url) }}" target="_blank" rel="noopener noreferrer" class="mb-2 inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                    <i class="fas fa-image" aria-hidden="true"></i> Ver comprobante
                                </a>
                            @endif
                        @endforeach
                    @endif
                    @if($venta->estado === 'PENDIENTE_PAGO' && Auth::user()->hasRole('Directiva'))
                        <form method="POST" action="/admin/ventas/{{ $venta->id_venta }}/confirmar-pago" onsubmit="return confirm('¿Confirmas que el pago ya se realizó?');">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full">
                                <i class="fas fa-check-circle mr-1"></i> Confirmar Pago
                            </button>
                        </form>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-8 text-center text-gray-500">
                    <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3 block"></i>
                    No se encontraron ventas con esos filtros.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection