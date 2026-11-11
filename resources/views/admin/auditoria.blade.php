@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Auditoría del Sistema</h1>
        <p class="text-gray-500 text-sm mt-1">Historial de las últimas 100 acciones realizadas en el sistema.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-4">Fecha / Hora</th>
                    <th class="p-4">Usuario</th>
                    <th class="p-4">Acción</th>
                    <th class="p-4">Entidad Afectada</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($registros as $reg)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 text-xs text-gray-500">
                        <i class="far fa-clock mr-1"></i> {{ $reg->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center font-bold text-xs">
                                {{ substr($reg->nombre_usuario ?? 'Sistema', 0, 1) }}
                            </div>
                            <span class="font-semibold text-gray-800">{{ $reg->nombre_usuario ?? 'Sistema' }}</span>
                        </div>
                    </td>
                    
                    <td class="p-4">
                        @if($reg->accion === 'CREAR')
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                <i class="fas fa-plus-circle mr-1"></i> Crear
                            </span>
                        @elseif($reg->accion === 'EDITAR')
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </span>
                        @elseif($reg->accion === 'ELIMINAR')
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                <i class="fas fa-trash-alt mr-1"></i> Eliminar
                            </span>
                        @elseif($reg->accion === 'PROMOVER')
                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                <i class="fas fa-arrow-up mr-1"></i> Promover
                            </span>
                        @elseif($reg->accion === 'RETIRAR')
                            <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                <i class="fas fa-arrow-down mr-1"></i> Retirar Cargo
                            </span>
                        @else
                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                {{ $reg->accion }}
                            </span>
                        @endif
                    </td>
                    
                    <td class="p-4">
                        <span class="font-medium text-gray-700 bg-gray-100 px-3 py-1 rounded-lg">
                            {{ $reg->entidad }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-12 text-center text-gray-500">
                        <i class="fas fa-history text-5xl text-gray-200 mb-3 block"></i>
                        <p class="text-lg font-semibold text-gray-600">No hay registros todavía</p>
                        <p class="text-sm">Las acciones de los usuarios aparecerán aquí automáticamente.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection