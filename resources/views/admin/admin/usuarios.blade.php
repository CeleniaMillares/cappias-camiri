@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Usuarios del Sistema (TI)</h1>
        <p class="text-gray-500 text-sm mt-1">Administra las credenciales de acceso y permisos.</p>
    </div>
    
    <!-- Botones de Acción -->
    <div class="flex gap-3 w-full md:w-auto">
        <a href="/admin/usuarios/promover" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition shadow-sm flex items-center justify-center gap-2 font-semibold text-sm flex-1 md:flex-none">
            <i class="fas fa-arrow-up"></i> Promover Socio
        </a>
        <a href="/admin/usuarios/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-2 font-semibold text-sm flex-1 md:flex-none">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
    </div>
</div>

<!-- Mensajes de Éxito o Error -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<!-- Tabla de Usuarios -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-4">Usuario</th>
                    <th class="p-4">Correo Electrónico</th>
                    <th class="p-4">Roles Asignados</th>
                    <th class="p-4 text-center">Estado</th>
                    <th class="p-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($usuarios as $user)
                <tr class="hover:bg-gray-50 transition">
                    <!-- Nombre con Avatar -->
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-sm">
                                {{ substr($user->nombre, 0, 1) }}
                            </div>
                            <span class="font-bold text-gray-800">{{ $user->nombre }}</span>
                        </div>
                    </td>
                    
                    <!-- Correo -->
                    <td class="p-4">{{ $user->correo }}</td>
                    
                    <!-- Roles -->
                    <td class="p-4">
                        @foreach($user->roles as $rol)
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-[11px] font-bold mr-1 uppercase tracking-wide">
                                {{ $rol->nombre }}
                            </span>
                        @endforeach
                    </td>
                    
                    <!-- Estado -->
                    <td class="p-4 text-center">
                        @if($user->activo)
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Activo</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Inactivo</span>
                        @endif
                    </td>
                    
                    <!-- Acciones -->
                    <td class="p-4">
                        <div class="flex justify-center gap-3">
                            <!-- Botón Editar -->
                            <a href="/admin/usuarios/{{ $user->id_usuario }}/editar" class="inline-flex items-center justify-center rounded-lg bg-blue-600 p-2 text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500" title="Editar" aria-label="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <!-- Botón Desactivar (Solo si no es el mismo usuario logueado) -->
                            @if(Auth::user()->id_usuario !== $user->id_usuario && $user->activo)
                            <form method="POST" action="/admin/usuarios/{{ $user->id_usuario }}" onsubmit="return confirm('¿Seguro que deseas desactivar a este usuario?');" class="inline">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 p-2 text-white shadow-sm transition-colors duration-200 hover:bg-red-700" title="Desactivar" aria-label="Desactivar usuario">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">
                        <i class="fas fa-users-slash text-4xl text-gray-300 mb-3 block"></i>
                        No hay usuarios registrados en el sistema.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection