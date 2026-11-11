@extends('layouts.app')

@section('content')
<div class="max-w-2xl">
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
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Precio (Bs):</label>
                <input type="number" step="0.01" min="0.01" name="precio" value="{{ old('precio') }}" class="w-full border p-2 rounded" required>
            </div>
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
        <input type="text" name="materiales" placeholder="Ej: Madera, barro" class="w-full border p-2 mb-4 rounded">

        <label class="block mb-2 font-semibold">Técnica:</label>
        <input type="text" name="tecnica" placeholder="Ej: Tallado a mano" class="w-full border p-2 mb-6 rounded">

        <label class="block mb-2 font-semibold">Fotografía (Opcional):</label>
        <input type="file" name="fotografia_url" accept="image/*" class="w-full border p-2 mb-6 rounded">

<button type="submit" id="btnGuardar" onclick="this.disabled=true; this.form.submit();" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 w-full">
    Guardar Producto
</button>
    </form>
</div>
@endsection