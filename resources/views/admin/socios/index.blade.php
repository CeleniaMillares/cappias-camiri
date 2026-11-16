@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Socios</h1>
        <p class="text-gray-500 text-sm mt-1">Administra las matrículas, mensualidades y el estado de los socios.</p>
    </div>
    <a href="/admin/socios/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2 font-semibold">
        <i class="fas fa-user-plus"></i> Inscribir Nuevo Socio
    </a>
</div>

<!-- Mensajes de éxito o error -->
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

<!-- Buscador -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" action="/admin/socios" class="flex gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Buscar Socio:</label>
            <input type="text" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre o CI del socio..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500 h-[42px]">
            <i class="fas fa-search mr-2"></i> Filtrar
        </button>
        <a href="/admin/socios" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600 h-[42px]">
            <i class="fas fa-times mr-2" aria-hidden="true"></i> Limpiar
        </a>
    </form>
</div>

<!-- Tabla de Socios -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Socio</th>
                <th class="p-4">CI</th>
                <th class="p-4">Inscripción</th>
                <th class="p-4">Estado Mes</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($socios as $socio)
            <tr class="hover:bg-gray-50 transition">
                <!-- Datos del Socio -->
                <td class="p-4">
                    <p class="font-bold text-gray-800">{{ $socio->usuario->nombre ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $socio->usuario->correo ?? '' }}</p>
                </td>
                
                <td class="p-4 font-medium">{{ $socio->ci }}</td>
                
                <td class="p-4 text-xs">
                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">
                        {{ \Carbon\Carbon::parse($socio->fecha_inscripcion)->format('d/m/Y') }}
                    </span>
                </td>
                
                <!-- Estado del Pago Mensual -->
                <td class="p-4">
                    @if($socio->pago_mes_actual)
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold border border-green-200">
                            <i class="fas fa-check-circle mr-1"></i> Al día
                        </span>
                    @else
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold border border-red-200">
                            <i class="fas fa-exclamation-circle mr-1"></i> Pendiente
                        </span>
                    @endif
                </td>
                
                <!-- Acciones -->
                <td class="p-4 flex flex-wrap gap-2 items-start">
                    
                    <!-- Botón para confirmar pago con detalle -->
                    @if($socio->cuotasPendientes->isNotEmpty())
                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 min-w-[220px]">
                            <p class="text-[10px] text-yellow-800 font-bold uppercase tracking-wide mb-1">
                                <i class="fas fa-clock"></i> Pago pendiente de verificar
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ implode(', ', $socio->conceptosPendientes) }}
                            </p>
                            <p class="text-xl font-extrabold text-yellow-900 mb-1">
                                Bs. {{ number_format($socio->totalPendiente, 2) }}
                            </p>
                            @if($socio->totalAbonado > 0)
                                <p class="text-[10px] text-gray-600 mb-1">
                                    Ya abonado: Bs. {{ number_format($socio->totalAbonado, 2) }} (efectivo)
                                </p>
                            @endif
                            <p class="text-[10px] text-gray-500 mb-2">
                                Método(s): {{ $socio->cuotasPendientes->pluck('metodo')->unique()->implode(', ') ?: 'N/A' }}
                            </p>
                            <form method="POST" action="/admin/socios/{{ $socio->id_socio }}/confirmar-pendientes" onsubmit="return confirm('¿Confirmas todos los pagos pendientes por un total de Bs. {{ number_format($socio->totalPendiente, 2) }}?');">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm w-full animate-pulse flex items-center justify-center gap-1">
                                    <i class="fas fa-check-double"></i>
                                    Confirmar Todos los Pagos
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="flex flex-col gap-2">
                        <!-- Botón Pagar Mensualidad -->
                        <a href="/admin/socios/{{ $socio->id_socio }}/pago-mensual" class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition shadow-sm text-center">
                            <i class="fas fa-money-bill-wave mr-1"></i> Pagar Mes
                        </a>
                        
                        <!-- Botón Historial -->
                        <a href="/admin/socios/{{ $socio->id_socio }}/historial" class="bg-gray-100 text-gray-700 border border-gray-200 px-3 py-2 rounded-lg text-xs font-bold hover:bg-gray-600 hover:text-white transition shadow-sm text-center">
                            <i class="fas fa-history mr-1"></i> Historial
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-500">
                    <i class="fas fa-users-slash text-4xl text-gray-300 mb-3 block"></i>
                    No se encontraron socios con esos filtros.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection