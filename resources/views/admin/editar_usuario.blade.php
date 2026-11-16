@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/usuarios" class="mb-2 inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Usuario</h1>
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
        <form method="POST" action="/admin/usuarios/{{ $usuario->id_usuario }}">
            @csrf 
            @method('PUT') <!-- Importante para actualizar -->
            
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Nombre Completo:</label>
                <input type="text" name="nombre" value="{{ $usuario->nombre }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Correo Electrónico:</label>
                <input type="email" name="correo" value="{{ $usuario->correo }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Nueva Contraseña (Opcional):</label>
                <input type="password" name="contrasena" placeholder="Dejar en blanco para mantener la actual" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
            </div>
            
            <div class="mb-5">
                <label class="block mb-3 font-semibold text-gray-700">Roles Asignados:</label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($roles as $rol)
                    <label class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                        <!-- Marcamos el checkbox si el usuario ya tiene este rol -->
                        <input type="checkbox" name="roles[]" value="{{ $rol->id_rol }}" 
                            {{ $usuario->roles->contains('id_rol', $rol->id_rol) ? 'checked' : '' }} 
                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="font-medium text-gray-700">{{ $rol->nombre }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-8 flex items-center gap-3 bg-blue-50 p-4 rounded-lg border border-blue-200">
                <input type="checkbox" name="activo" id="activo" value="1" {{ $usuario->activo ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                <label for="activo" class="font-bold text-blue-800 cursor-pointer">Usuario Activo (puede iniciar sesión)</label>
            </div>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full flex justify-center items-center gap-2">
                <i class="fas fa-save text-lg"></i> Actualizar Usuario
            </button>
        </form>
    </div>
</div>
@endsection