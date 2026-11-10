@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <a href="/admin/alumnos" class="text-blue-600 hover:underline text-sm">← Volver a Alumnos</a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-6">Editar Alumno</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/admin/alumnos/{{ $alumno->id_alumno }}" class="bg-white p-6 rounded-xl shadow-sm border">
        @csrf
        @method('PUT')
        <label class="block mb-1 font-semibold">Nombre</label>
        <input name="nombres" value="{{ old('nombres', $alumno->nombres) }}" class="w-full border p-2 rounded-lg mb-4" required maxlength="150">
        <label class="block mb-1 font-semibold">CI</label>
        <input name="ci" value="{{ old('ci', $alumno->ci) }}" class="w-full border p-2 rounded-lg mb-4" required maxlength="20">
        <label class="block mb-1 font-semibold">Teléfono</label>
        <input name="telefono" value="{{ old('telefono', $alumno->telefono) }}" class="w-full border p-2 rounded-lg mb-4" required maxlength="20">
        <label class="block mb-1 font-semibold">Edad</label>
        <input type="number" name="edad" value="{{ old('edad', $alumno->edad) }}" min="1" class="w-full border p-2 rounded-lg mb-6" required>
        <button class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg font-bold">Guardar Cambios</button>
    </form>
</div>
@endsection
