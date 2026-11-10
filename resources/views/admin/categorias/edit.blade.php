@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="/admin/categorias" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Categoría</h1>
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

    <form method="POST" action="/admin/categorias/{{ $categoria->id_categoria }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        @method('PUT')
        
        <div class="mb-5">
            <label class="block mb-2 font-semibold text-gray-700">Nombre:</label>
            <input type="text" name="nombre" value="{{ $categoria->nombre }}" class="w-full border-gray-300 border p-3 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-gray-700">Descripción:</label>
            <textarea name="descripcion" rows="3" class="w-full border-gray-300 border p-3 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ $categoria->descripcion }}</textarea>
        </div>

        <!-- 🔥 CASILLA PARA ACTIVAR / DESACTIVAR -->
        <div class="mb-6 flex items-center gap-3 bg-blue-50 p-4 rounded-lg border border-blue-200">
            <input type="checkbox" name="activo" id="activo" value="1" {{ $categoria->activo ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
            <label for="activo" class="font-bold text-blue-800 cursor-pointer">Categoría Activa (visible para productos)</label>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition w-full font-bold shadow-sm">
            <i class="fas fa-save mr-2"></i> Actualizar Categoría
        </button>
    </form>
</div>
@endsection