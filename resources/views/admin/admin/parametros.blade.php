@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Parámetros del Sistema</h1>
            <p class="text-gray-500 text-sm mt-1">Configura las comisiones, cupos y valores globales.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="/admin/parametros">
            @csrf
            
            @foreach($parametros as $param)
            <div class="mb-6">
                <label class="block mb-1 font-bold text-gray-700 capitalize">
                    <i class="fas fa-cog text-blue-500 mr-2"></i>{{ str_replace('_', ' ', $param->clave) }}:
                </label>
                <p class="text-xs text-gray-500 mb-2">{{ $param->descripcion }}</p>
                <input type="text" name="parametros[{{ $param->id_parametro }}]" value="{{ $param->valor }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>
            @endforeach

            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition w-full font-bold shadow-sm mt-4">
                <i class="fas fa-save mr-2"></i> Guardar Cambios
            </button>
        </form>
    </div>
</div>
@endsection