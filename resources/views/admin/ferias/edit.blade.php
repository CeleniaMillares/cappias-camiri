@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/ferias" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Feria</h1>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="/admin/ferias/{{ $feria->id_feria }}">
            @csrf 
            @method('PUT') <!-- Importante para actualizar -->
            
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Nombre de la Feria:</label>
                <input type="text" name="nombre" value="{{ $feria->nombre }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Fecha del Evento:</label>
                <input type="date" name="fecha" value="{{ \Carbon\Carbon::parse($feria->fecha)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-8">
                <label class="block mb-2 font-semibold text-gray-700">Lugar:</label>
                <input type="text" name="lugar" value="{{ $feria->lugar }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition w-full font-bold shadow-sm flex justify-center items-center gap-2">
                <i class="fas fa-save text-lg"></i> Actualizar Feria
            </button>
        </form>
    </div>
</div>
@endsection