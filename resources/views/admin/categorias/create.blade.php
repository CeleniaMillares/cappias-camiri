@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="/admin/categorias" class="mb-2 inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Categoría</h1>
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

    <form method="POST" action="/admin/categorias" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        <div class="mb-5">
            <label class="block mb-2 font-semibold text-gray-700">Nombre:</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border-gray-300 border p-3 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
        </div>
        
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-gray-700">Descripción:</label>
            <textarea name="descripcion" rows="3" class="w-full border-gray-300 border p-3 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('descripcion') }}</textarea>
        </div>
        
        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition w-full font-bold shadow-sm">
            <i class="fas fa-save mr-2"></i> Guardar Categoría
        </button>
    </form>
</div>
@endsection