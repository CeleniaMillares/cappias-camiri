@extends('layouts.app')

@section('content')
<div class="max-w-2xl">
    <a href="/admin/productos" class="mb-4 inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600">
        <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Volver a Productos
    </a>
    <h1 class="text-2xl font-bold mb-6">Registrar Nuevo Producto</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="/admin/productos" class="bg-white p-6 rounded shadow" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold">Nombre:</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Jarra de barro" class="w-full border p-2 rounded" required>
                <p class="mt-1 text-xs text-gray-500">Escribe el nombre del producto artesanal.</p>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Precio (Bs):</label>
                <input type="number" step="0.01" min="0.01" name="precio" value="{{ old('precio') }}" placeholder="Ej: 15.00" class="w-full border p-2 rounded" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-semibold">Descripción:</label>
            <textarea name="descripcion" rows="3" placeholder="Describe brevemente el producto y sus características." class="w-full border p-2 rounded">{{ old('descripcion') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold">Categoría:</label>
                <select name="id_categoria" class="w-full border p-2 rounded" required>
                    <option value="">Seleccione...</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria }}" @selected(old('id_categoria') == $cat->id_categoria)>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Socio Responsable:</label>
                <select name="id_usuario_socio" class="w-full border p-2 rounded" required>
                    <option value="">Seleccione...</option>
                    @foreach($socios as $soc)
                        <option value="{{ $soc->id_usuario }}" @selected(old('id_usuario_socio') == $soc->id_usuario)>{{ $soc->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold">Stock Actual:</label>
                <input type="number" min="0" name="stock_actual" value="{{ old('stock_actual') }}" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Stock Mínimo (Alerta):</label>
                <input type="number" min="1" name="stock_minimo" value="{{ old('stock_minimo', 5) }}" class="w-full border p-2 rounded" required>
            </div>
        </div>

        <label class="block mb-2 font-semibold">Materiales:</label>
        <input type="text" name="materiales" value="{{ old('materiales') }}" placeholder="Ej: Madera, barro" class="w-full border p-2 mb-4 rounded">

        <label class="block mb-2 font-semibold">Técnica:</label>
        <input type="text" name="tecnica" value="{{ old('tecnica') }}" placeholder="Ej: Tallado a mano" class="w-full border p-2 mb-6 rounded">

        <label class="block mb-2 font-semibold">Fotografía (Opcional):</label>
        <input type="file" name="fotografia_url" accept="image/*" class="w-full border p-2 mb-6 rounded">

<button type="submit" id="btnGuardar" onclick="this.disabled=true; this.form.submit();" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full">
    <i class="fas fa-save mr-2" aria-hidden="true"></i> Guardar Producto
</button>
    </form>
</div>
@endsection