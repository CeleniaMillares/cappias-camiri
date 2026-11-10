@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/ferias" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Registrar Nueva Feria</h1>
    </div>

    <!-- Mostrar errores de validación si los hay -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="/admin/ferias">
            @csrf
            
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Nombre de la Feria:</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Feria de Artesanías 2026" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Fecha del Evento:</label>
                <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-8">
                <label class="block mb-2 font-semibold text-gray-700">Lugar:</label>
                <input type="text" name="lugar" value="{{ old('lugar') }}" placeholder="Ej: Plaza Principal de Camiri" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-gray-700">Imagen de Publicidad (Opcional):</label>
                <input type="file" name="imagen_url" accept="image/*" class="w-full border p-2 rounded bg-gray-50">
                <p class="text-xs text-gray-500 mt-1">Sube un afiche para mostrar en la web.</p>
            </div>

            <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition w-full font-bold shadow-sm flex justify-center items-center gap-2">
                <i class="fas fa-save text-lg"></i> Guardar Feria
            </button>
        </form>
    </div>
</div>
@endsection