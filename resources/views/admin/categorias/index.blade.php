@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Categorías</h1>
        <p class="text-gray-500 text-sm mt-1">Administra las categorías de los productos artesanales.</p>
    </div>
    
    <a href="/admin/categorias/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i> Nueva Categoría
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Nombre</th>
                <th class="p-4">Descripción</th>
                <th class="p-4">Estado</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($categorias as $cat)
            <tr class="hover:bg-gray-50 transition">
                <td class="p-4 font-bold text-gray-800">{{ $cat->nombre }}</td>
                <td class="p-4">{{ $cat->descripcion ?? 'Sin descripción' }}</td>
                <td class="p-4">
                    @if($cat->activo)
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold border border-green-200">Activa</span>
                    @else
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold border border-red-200">Inactiva</span>
                    @endif
                </td>
                <td class="p-4 flex gap-3 items-center">
                    <!-- Botón Editar -->
                    <a href="/admin/categorias/{{ $cat->id_categoria }}/editar" class="text-blue-600 hover:underline font-semibold">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    
                    @if($cat->activo)
                        <!-- Botón Desactivar (Si está activa) -->
                        <form method="POST" action="/admin/categorias/{{ $cat->id_categoria }}" onsubmit="return confirm('¿Desactivar esta categoría? Los productos asociados ya no se mostrarán en las ventas.');">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline font-semibold">
                                <i class="fas fa-ban"></i> Desactivar
                            </button>
                        </form>
                    @else
                        <!-- Botón Activar (Si está inactiva) -->
                        <form method="POST" action="/admin/categorias/{{ $cat->id_categoria }}" onsubmit="return confirm('¿Activar esta categoría nuevamente?');">
                            @csrf 
                            @method('PUT')
                            <input type="hidden" name="nombre" value="{{ $cat->nombre }}">
                            <input type="hidden" name="descripcion" value="{{ $cat->descripcion }}">
                            <input type="hidden" name="activo" value="1">
                            <button type="submit" class="text-green-600 hover:underline font-semibold">
                                <i class="fas fa-check-circle"></i> Activar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="p-8 text-center text-gray-500">
                    <i class="fas fa-tags text-4xl text-gray-300 mb-3 block"></i>
                    No hay categorías registradas todavía.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection