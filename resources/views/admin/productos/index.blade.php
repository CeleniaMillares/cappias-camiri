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
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500 h-[42px]">
            <i class="fas fa-search mr-2"></i> Filtrar
        </button>
        <a href="/admin/productos" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600 h-[42px]">
            <i class="fas fa-times mr-2" aria-hidden="true"></i> Limpiar
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
                <td class="p-4">
                    <div class="flex flex-wrap gap-2 items-center">
                    <button type="button" data-stock-open data-product-id="{{ $prod->id_producto }}" data-product-name="{{ $prod->nombre }}" data-product-stock="{{ $prod->stock_actual }}" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500">
                        <i class="fas fa-plus" aria-hidden="true"></i> Añadir Stock
                    </button>
                    <a href="/admin/productos/{{ $prod->id_producto }}/editar" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500"><i class="fas fa-edit" aria-hidden="true"></i> Editar</a>
                    
                    <form method="POST" action="/admin/productos/{{ $prod->id_producto }}" onsubmit="return confirm('¿Desactivar este producto? Ya no aparecerá en las ventas.');">
                        @csrf 
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-red-700"><i class="fas fa-ban" aria-hidden="true"></i> Desactivar</button>
                    </form>
                    </div>
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

@if(Auth::user()->hasRole('Directiva'))
<dialog id="stock-modal" class="w-full max-w-md rounded-xl border-0 bg-white p-0 shadow-2xl backdrop:bg-slate-950/60 dark:bg-cappias-surface">
    <div class="p-6">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h2 id="stock-modal-title" class="text-lg font-bold text-gray-800">Añadir Stock</h2>
                <p id="stock-product-name" class="mt-1 text-sm text-gray-500"></p>
                <p id="stock-current" class="mt-1 text-xs text-gray-500"></p>
            </div>
            <button type="button" data-stock-close class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Cerrar">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <form id="stock-form" method="POST">
            @csrf
            <label for="stock-quantity" class="mb-2 block text-sm font-semibold text-gray-700">Cantidad a sumar</label>
            <input id="stock-quantity" type="number" name="cantidad" min="1" step="1" value="1" required class="mb-5 w-full rounded-lg border p-3">
            <div class="flex justify-end gap-3">
                <button type="button" data-stock-close class="rounded-lg bg-gray-200 px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancelar</button>
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700">Guardar Stock</button>
            </div>
        </form>
    </div>
</dialog>
<script>
    (function () {
        const modal = document.getElementById('stock-modal');
        const form = document.getElementById('stock-form');

        document.querySelectorAll('[data-stock-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                form.action = '/admin/productos/' + encodeURIComponent(button.dataset.productId) + '/stock';
                document.getElementById('stock-product-name').textContent = button.dataset.productName;
                document.getElementById('stock-current').textContent = 'Stock actual: ' + button.dataset.productStock;
                document.getElementById('stock-quantity').value = '1';
                modal.showModal();
            });
        });

        document.querySelectorAll('[data-stock-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                modal.close();
            });
        });
    })();
</script>
@endif
@endsection