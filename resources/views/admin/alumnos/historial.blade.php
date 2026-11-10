@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="/admin/alumnos" class="text-blue-600 hover:underline text-sm">← Volver a Alumnos</a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2">Historial de {{ $alumno->nombres }}</h1>
    <p class="text-sm text-gray-500">CI: {{ $alumno->ci }}</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-4">Curso</th>
                    <th class="p-4">Horario</th>
                    <th class="p-4">Estado</th>
                    <th class="p-4">Pagos</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($inscripciones as $inscripcion)
                    <tr>
                        <td class="p-4 font-semibold">{{ $inscripcion->horarioCurso->curso->nombre }}</td>
                        <td class="p-4">{{ $inscripcion->horarioCurso->dia_semana }} {{ $inscripcion->horarioCurso->hora_inicio }}–{{ $inscripcion->horarioCurso->hora_fin }}</td>
                        <td class="p-4">{{ $inscripcion->estado }}</td>
                        <td class="p-4">
                            @forelse($inscripcion->pagos as $pago)
                                <div>{{ $pago->metodo }} — Bs. {{ number_format($pago->monto, 2) }} ({{ $pago->estado }})</div>
                            @empty
                                <span class="text-gray-400">Sin pagos</span>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-500">No hay cursos registrados en el historial.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
