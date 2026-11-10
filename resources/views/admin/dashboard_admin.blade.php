@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-gray-800">Panel de Administración TI</h1>
    <p class="text-gray-500 mt-1">Resumen del estado técnico y seguridad del sistema.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-indigo-500 flex items-center justify-between">
        <div>
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wide">Usuarios en el Sistema</h3>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $totalUsuarios }}</p>
        </div>
        <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-xl">
            <i class="fas fa-users-cog"></i>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-orange-500 flex items-center justify-between">
        <div>
            <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wide">Acciones Auditadas</h3>
            <p class="text-3xl font-extrabold text-gray-800 mt-1">{{ $totalAuditorias }}</p>
        </div>
        <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center text-xl">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-history text-indigo-500"></i> Últimas Acciones en el Sistema
    </h2>
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-3">Usuario</th>
                <th class="p-3">Acción</th>
                <th class="p-3">Entidad</th>
                <th class="p-3">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($ultimasAcciones as $accion)
            <tr>
                <td class="p-3 font-semibold">{{ $accion->usuario->nombre ?? 'Sistema' }}</td>
                <td class="p-3">{{ $accion->accion }}</td>
                <td class="p-3">{{ $accion->entidad }}</td>
                <td class="p-3 text-gray-500">{{ $accion->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-3 text-center text-gray-500">No hay acciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection