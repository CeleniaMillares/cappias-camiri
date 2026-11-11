@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Productos</h1>
        <p class="text-gray-500 text-sm mt-1">Administra el catálogo de artesanías y productos de CAPPIAS.</p>
    </div>
    
    <!-- Solo la Directiva puede crear productos -->
    @if(Auth::user()->hasRole('Directiva'))
    <a href="/admin/productos/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i> Nuevo Producto
    </a>
    @endif
</div>

<!-- BARRA DE FILTROS -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form method="GET" action="/admin/productos" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Buscar por nombre:</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej: Tazas, Jarra..." class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Categoría:</label>
            <select name="id_categoria" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id_categoria }}" {{ request('id_categoria') == $cat->id_categoria ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Socio:</label>
            <select name="id_usuario_socio" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos los socios</option>
                @foreach($socios as $soc)
                    <option value="{{ $soc->id_usuario }}" {{ request('id_usuario_socio') == $soc->id_usuario ? 'selected' : '' }}>{{ $soc->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 transition font-semibold shadow-sm h-[42px]">
            <i class="fas fa-search mr-2"></i> Filtrar
        </button>
        <a href="/admin/productos" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition font-semibold shadow-sm h-[42px] flex items-center justify-center">
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
                <th class="p-4">Producto</th>
                <th class="p-4">Categoría</th>
                <th class="p-4">Socio Responsable</th>
                <th class="p-4">Precio</th>
                <th class="p-4">Stock</th>
                
                <!-- La columna de acciones solo existe para la Directiva -->
                @if(Auth::user()->hasRole('Directiva'))
                <th class="p-4">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($productos as $prod)
            <tr class="hover:bg-gray-50 transition">
                <!-- Columna de Producto: Muestra materiales/técnica para diferenciar homónimos -->
                <td class="p-4">
                    @if($prod->fotografia_url)
                        <img src="{{ asset('storage/' . $prod->fotografia_url) }}" alt="{{ $prod->nombre }}" class="w-16 h-16 object-cover rounded mb-2">
                    @endif
                    <p class="font-bold text-gray-800">{{ $prod->nombre }}</p>
                    @if($prod->materiales || $prod->tecnica)
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $prod->materiales ?? '' }} {{ $prod->tecnica ? '- ' . $prod->tecnica : '' }}
                        </p>
                    @endif
                </td>
                <td class="p-4">{{ $prod->categoria->nombre ?? 'N/A' }}</td>
                <td class="p-4">{{ $prod->socio->nombre ?? 'N/A' }}</td>
                <td class="p-4 font-semibold text-gray-800">Bs. {{ number_format($prod->precio, 2) }}</td>
                <td class="p-4">
                    @if($prod->stock_actual <= $prod->stock_minimo)
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold border border-red-200">
                            {{ $prod->stock_actual }} (Bajo)
                        </span>
                    @else
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold border border-green-200">
                            {{ $prod->stock_actual }}
                        </span>
                    @endif
                </td>

                <!-- Acciones exclusivas de la Directiva -->
                @if(Auth::user()->hasRole('Directiva'))
                <td class="p-4 flex gap-3 items-center">
                    <a href="/admin/productos/{{ $prod->id_producto }}/editar" class="text-blue-600 hover:underline font-semibold">Editar</a>
                    
                    <form method="POST" action="/admin/productos/{{ $prod->id_producto }}" onsubmit="return confirm('¿Desactivar este producto? Ya no aparecerá en las ventas.');">
                        @csrf 
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline font-semibold">Desactivar</button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ Auth::user()->hasRole('Directiva') ? '6' : '5' }}" class="p-8 text-center text-gray-500">
                    <i class="fas fa-box-open text-4xl text-gray-300 mb-3 block"></i>
                    No se encontraron productos que coincidan con la búsqueda.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $productos->links() }}
</div>
@endsection