@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/productos" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Producto</h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/admin/productos/{{ $producto->id_producto }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" enctype="multipart/form-data">
        @csrf 
        @method('PUT')
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Nombre:</label>
                <input type="text" name="nombre" value="{{ $producto->nombre }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Precio (Bs):</label>
                <input type="number" step="0.01" min="0.01" name="precio" value="{{ $producto->precio }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Categoría:</label>
                <select name="id_categoria" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria }}" {{ $producto->id_categoria == $cat->id_categoria ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Socio Responsable:</label>
                <select name="id_usuario_socio" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    @foreach($socios as $soc)
                        <option value="{{ $soc->id_usuario }}" {{ $producto->id_usuario_socio == $soc->id_usuario ? 'selected' : '' }}>{{ $soc->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Stock Actual:</label>
                <input type="number" min="0" name="stock_actual" value="{{ $producto->stock_actual }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Stock Mínimo:</label>
                <input type="number" min="1" name="stock_minimo" value="{{ $producto->stock_minimo }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700">Descripción:</label>
            <textarea name="descripcion" rows="2" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ $producto->descripcion }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Materiales:</label>
                <input type="text" name="materiales" value="{{ $producto->materiales }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Técnica:</label>
                <input type="text" name="tecnica" value="{{ $producto->tecnica }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Si tienes imagen de producto, puedes agregarla aquí -->
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-gray-700">Fotografía (Opcional):</label>
            <input type="file" name="fotografia_url" accept="image/*" class="w-full border p-2 rounded-lg bg-gray-50">
            @if($producto->fotografia_url)
                <p class="text-xs text-gray-500 mt-1">Imagen actual: <a href="{{ asset('storage/' . $producto->fotografia_url) }}" target="_blank" class="text-blue-600 underline">Ver</a></p>
            @endif
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition w-full font-bold shadow-sm">
            <i class="fas fa-save mr-2"></i> Actualizar Producto
        </button>
    </form>
</div>
@endsection