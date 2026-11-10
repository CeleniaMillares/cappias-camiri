@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="/admin/cursos" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista de cursos
    </a>
    <h1 class="text-2xl font-bold text-gray-800">Alumnos Inscritos</h1>
    <p class="text-lg text-gray-600 mt-1"><i class="fas fa-book text-gray-400 mr-2"></i> {{ $curso->nombre }}</p>
</div>

<div class="space-y-6">
    @forelse($curso->horarios as $horario)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Encabezado del Horario -->
        <div class="bg-gray-50 p-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="font-bold text-gray-800"><i class="far fa-clock text-blue-500 mr-2"></i> {{ $horario->dia_semana }} ({{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }})</h2>
                <p class="text-xs text-gray-500 mt-1">Inicio: {{ \Carbon\Carbon::parse($horario->fecha_inicio)->format('d/m/Y') }} | Fin: {{ \Carbon\Carbon::parse($horario->fecha_fin)->format('d/m/Y') }}</p>
            </div>
            <div class="text-right">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                    {{ $horario->inscripciones->count() }} / {{ $horario->cupo_maximo }} Cupos
                </span>
            </div>
        </div>

        <!-- Tabla de Alumnos SOLO de este horario -->
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-white text-gray-500 border-b border-gray-100">
                <tr>
                    <th class="p-3 font-semibold">Nombre del Alumno</th>
                    <th class="p-3 font-semibold">CI</th>
                    <th class="p-3 font-semibold">Teléfono</th>
                    <th class="p-3 font-semibold text-center">Estado de Pago</th>
                    <th class="p-3 font-semibold">Método de Pago</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($horario->inscripciones as $inscripcion)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-medium text-gray-800">{{ $inscripcion->alumno->nombres }}</td>
                    <td class="p-3">{{ $inscripcion->alumno->ci }}</td>
                    <td class="p-3">{{ $inscripcion->alumno->telefono }}</td>
                    <td class="p-3 text-center">
                        @if($inscripcion->estado === 'CONFIRMADA')
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Confirmado</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">PENDIENTE_PAGO — cupo reservado, pago por verificar</span>
                        @endif
                    </td>
                    <td class="p-3">
                        @forelse($inscripcion->pagos as $pago)
                            <div class="mb-1">
                                <span class="font-medium">{{ $pago->metodo }}</span>
                                <span>Bs. {{ number_format($pago->monto, 2) }}</span>
                                <span class="text-xs {{ $pago->estado === 'CONFIRMADO' ? 'text-green-700' : 'text-yellow-700' }}">
                                    ({{ $pago->estado === 'CONFIRMADO' ? 'Verificado' : 'Pendiente' }})
                                </span>
                            </div>
                        @empty
                            <span class="text-xs text-gray-400">Sin pago registrado</span>
                        @endforelse
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-6 text-center text-gray-500">
                        No hay alumnos inscritos en este horario todavía.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @empty
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 text-center text-gray-500">
        Este curso no tiene horarios registrados.
    </div>
    @endforelse
</div>
@endsection