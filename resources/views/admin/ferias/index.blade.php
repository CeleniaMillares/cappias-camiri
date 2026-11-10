@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Ferias</h1>
        <p class="text-gray-500 text-sm mt-1">Administra los eventos donde se realizan ventas o inscripciones.</p>
    </div>
    <!-- 🔥 Enlace corregido a /crear (con "a") -->
    <a href="/admin/ferias/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i> Nueva Feria
    </a>
</div>

<!-- BARRA DE FILTROS -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" action="/admin/ferias" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Buscar por nombre o lugar:</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: Plaza Principal..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha del evento:</label>
            <input type="date" name="fecha" value="{{ request('fecha') }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 transition font-semibold shadow-sm h-[42px]">
            <i class="fas fa-search mr-2"></i> Filtrar
        </button>
        <a href="/admin/ferias" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition font-semibold shadow-sm h-[42px] flex items-center justify-center">
            Limpiar
        </a>
    </form>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Publicidad</th>
                <th class="p-4">Nombre de la Feria</th>
                <th class="p-4">Fecha del Evento</th>
                <th class="p-4">Lugar</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($ferias as $feria)
            <tr class="hover:bg-gray-50 transition">
                <td class="p-4">
                    @if($feria->imagen_url)
                        <img src="{{ asset('storage/' . $feria->imagen_url) }}" class="w-16 h-16 object-cover rounded shadow-sm border border-gray-200" alt="Publicidad">
                    @else
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">Sin imagen</span>
                    @endif
                </td>
                <td class="p-4 font-bold text-gray-800">{{ $feria->nombre }}</td>
                <td class="p-4">
                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                        <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($feria->fecha)->format('d/m/Y') }}
                    </span>
                </td>
                <td class="p-4"><i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>{{ $feria->lugar }}</td>
                <td class="p-4 flex gap-3 items-center">
                    <!-- 🔥 Enlace corregido a /editar (con "a") -->
                    <a href="/admin/ferias/{{ $feria->id_feria }}/editar" class="text-blue-600 hover:underline font-semibold">Editar</a>
                    
                    <form method="POST" action="/admin/ferias/{{ $feria->id_feria }}" onsubmit="return confirm('¿Estás seguro de eliminar esta feria?');">
                        @csrf 
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline font-semibold">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-500">
                    <i class="fas fa-store text-4xl text-gray-300 mb-3 block"></i>
                    No se encontraron ferias con esos filtros.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection