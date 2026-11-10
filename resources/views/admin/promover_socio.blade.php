@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="/admin/usuarios" class="text-blue-600 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver a Usuarios</a>
        <h1 class="text-2xl font-bold text-gray-800">Cargos de Directiva</h1>
        <p class="text-gray-500 text-sm mt-1">Asigna o retira el cargo de Directiva a los socios de CAPPIAS.</p>
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
                    <th class="p-4">Socio</th>
                    <th class="p-4">Roles Actuales</th>
                    <th class="p-4 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($socios as $socio)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4">
                        <p class="font-bold text-gray-800">{{ $socio->usuario->nombre ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">CI: {{ $socio->ci }}</p>
                    </td>
                    <td class="p-4">
                        @foreach($socio->usuario->roles as $rol)
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-[11px] font-bold mr-1 uppercase tracking-wide">{{ $rol->nombre }}</span>
                        @endforeach
                    </td>
                    <td class="p-4 text-center">
                        @if($socio->usuario->roles->contains('id_rol', $rolDirectiva->id_rol))
                            <!-- Botón para Retirar el Cargo -->
                            <form method="POST" action="/admin/usuarios/retirar-directiva/{{ $socio->id_socio }}">
                                @csrf
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-red-700 shadow-sm" onclick="return confirm('¿Estás seguro de retirar el cargo de Directiva a este socio? Seguirá siendo Socio.');">
                                    <i class="fas fa-arrow-down mr-1"></i> Retirar Cargo
                                </button>
                            </form>
                        @else
                            <!-- Botón para Hacer Directiva -->
                            <form method="POST" action="/admin/usuarios/promover/{{ $socio->id_socio }}">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm" onclick="return confirm('¿Dar permisos de Directiva a este socio?');">
                                    <i class="fas fa-arrow-up mr-1"></i> Hacer Directiva
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="p-8 text-center text-gray-500">No hay socios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection