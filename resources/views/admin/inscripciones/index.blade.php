@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Inscripciones</h1>
        <p class="text-gray-500 text-sm mt-1">Administra los alumnos inscritos en los cursos.</p>
    </div>
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
                <th class="p-4">Alumno</th>
                <th class="p-4">Curso</th>
                <th class="p-4">Estado</th>
                <th class="p-4">Pago</th>
                @if(Auth::user()->hasRole('Directiva'))
                <th class="p-4">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($inscripciones as $ins)
            <tr class="hover:bg-gray-50 transition">
                <td class="p-4">
                    <span class="font-bold text-gray-800">{{ $ins->alumno->nombres }}</span> <br>
                    <span class="text-xs text-gray-500">CI: {{ $ins->alumno->ci }}</span>
                </td>
                <td class="p-4">{{ $ins->horarioCurso->curso->nombre }}</td>
                <td class="p-4">
                    @if($ins->estado == 'CONFIRMADA')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold border border-green-200">Confirmada</span>
                    @else
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold border border-yellow-200">PENDIENTE_PAGO — cupo reservado, pago por verificar</span>
                    @endif
                </td>
                <td class="p-4">
                    @forelse($ins->pagos as $pago)
                        <div class="mb-1">
                            <span class="font-medium">{{ $pago->metodo }}</span>
                            <span>Bs. {{ number_format($pago->monto, 2) }}</span>
                            <span class="text-xs {{ $pago->estado === 'CONFIRMADO' ? 'text-green-700' : 'text-yellow-700' }}">
                                ({{ $pago->estado === 'CONFIRMADO' ? 'Verificado' : 'Pendiente de verificación' }})
                            </span>
                        </div>
                    @empty
                        <span class="text-xs text-gray-400">Sin pago registrado</span>
                    @endforelse
                </td>
                
                @if(Auth::user()->hasRole('Directiva'))
                <td class="p-4">
                    @if($ins->estado == 'PENDIENTE_PAGO')
                        <form method="POST" action="/admin/inscripciones/{{ $ins->id_inscripcion }}/confirmar" onsubmit="return confirm('¿Confirmas que el pago ya fue recibido? Se calculará la comisión del 20%.');">
                            @csrf
                            <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                                <i class="fas fa-check-circle mr-1"></i> Confirmar Pago
                            </button>
                        </form>
                    @else
                        <span class="text-xs text-gray-400">Completado</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ Auth::user()->hasRole('Directiva') ? 5 : 4 }}" class="p-8 text-center text-gray-500">
                    <i class="fas fa-user-graduate text-4xl text-gray-300 mb-3 block"></i>
                    No hay inscripciones registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection