@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/cursos" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Curso</h1>
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

    <form method="POST" action="/admin/cursos/{{ $curso->id_curso }}" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" enctype="multipart/form-data">
        @csrf 
        @method('PUT')
        
        <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700">Instructor:</label>
            <select name="id_usuario_instructor" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                @foreach($instructores as $inst)
                    <option value="{{ $inst->id_usuario }}" {{ $curso->id_usuario_instructor == $inst->id_usuario ? 'selected' : '' }}>{{ $inst->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Nombre del Curso:</label>
                <input type="text" name="nombre" value="{{ $curso->nombre }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700">Costo (Bs):</label>
                <input type="number" step="0.01" name="costo" value="{{ $curso->costo }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700">Duración:</label>
            <input type="text" name="duracion" value="{{ $curso->duracion }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <!-- Imagen de Publicidad (si la tienes) -->
        <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700">Imagen de Publicidad (Opcional):</label>
            <input type="file" name="imagen_url" accept="image/*" class="w-full border p-2 rounded-lg bg-gray-50">
            @if($curso->imagen_url)
                <p class="text-xs text-gray-500 mt-1">Imagen actual: <a href="{{ asset('storage/' . $curso->imagen_url) }}" target="_blank" class="text-blue-600 underline">Ver</a></p>
            @endif
        </div>

        @if($horario)
        <h3 class="text-lg font-bold border-b pb-2 mb-4 mt-6">Horario</h3>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block mb-1 text-sm">Día:</label>
                <input type="text" name="horarios[0][dia_semana]" value="{{ $horario->dia_semana }}" class="w-full border p-2 rounded-lg" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Cupo Máximo:</label>
                <input type="number" name="horarios[0][cupo_maximo]" value="{{ $horario->cupo_maximo }}" min="{{ $cupoMin }}" max="{{ $cupoMax }}" class="w-full border p-2 rounded-lg" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Hora Inicio:</label>
                <input type="time" name="horarios[0][hora_inicio]" value="{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}" class="w-full border p-2 rounded-lg" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Hora Fin:</label>
                <input type="time" name="horarios[0][hora_fin]" value="{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}" class="w-full border p-2 rounded-lg" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Fecha Inicio:</label>
                <input type="date" name="horarios[0][fecha_inicio]" value="{{ $horario->fecha_inicio }}" class="w-full border p-2 rounded-lg" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Fecha Fin:</label>
                <input type="date" name="horarios[0][fecha_fin]" value="{{ $horario->fecha_fin }}" class="w-full border p-2 rounded-lg" required>
            </div>
        </div>
        @endif

        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition w-full font-bold shadow-sm">
            <i class="fas fa-save mr-2"></i> Actualizar Curso
        </button>
    </form>
</div>
@endsection