@extends('layouts.app')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Registrar Nuevo Curso</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="/admin/cursos" class="bg-white p-6 rounded shadow">
        @csrf
        
        <label class="block mb-2 font-semibold">Instructor:</label>
        <select name="id_usuario_instructor" class="w-full border p-2 mb-4 rounded" required>
            <option value="">Seleccione...</option>
            @foreach($instructores as $inst)
                <option value="{{ $inst->id_usuario }}">{{ $inst->nombre }}</option>
            @endforeach
        </select>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-2 font-semibold">Nombre del Curso:</label>
                <input type="text" name="nombre" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Costo (Bs):</label>
                <input type="number" name="costo" class="w-full border p-2 rounded" required>
            </div>
        </div>

        <label class="block mb-2 font-semibold">Duración:</label>
        <input type="text" name="duracion" placeholder="Ej: 4 semanas" class="w-full border p-2 mb-6 rounded" required>

        <h3 class="text-lg font-bold border-b pb-2 mb-4">Horario</h3>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1 text-sm">Día:</label>
                <input type="text" name="horarios[0][dia_semana]" placeholder="Sábado" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Cupo Máximo:</label>
                <input type="number" name="horarios[0][cupo_maximo]" value="15" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Hora Inicio:</label>
                <input type="time" name="horarios[0][hora_inicio]" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Hora Fin:</label>
                <input type="time" name="horarios[0][hora_fin]" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Fecha Inicio:</label>
                <input type="date" name="horarios[0][fecha_inicio]" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 text-sm">Fecha Fin:</label>
                <input type="date" name="horarios[0][fecha_fin]" class="w-full border p-2 rounded" required>
            </div>
        </div>

        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 mt-4 w-full">Guardar Curso</button>
    </form>
</div>
@endsection