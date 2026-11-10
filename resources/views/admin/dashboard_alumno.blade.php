@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Mis Cursos</h1>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse($inscripciones as $ins)
    <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500">
        <h3 class="font-bold text-lg">{{ $ins->horarioCurso->curso->nombre }}</h3>
        <p class="text-gray-500 text-sm">Instructor: {{ $ins->horarioCurso->curso->instructor->nombre }}</p>
        <p class="text-gray-500 text-sm">Horario: {{ $ins->horarioCurso->dia_semana }} de {{ $ins->horarioCurso->hora_inicio }} a {{ $ins->horarioCurso->hora_fin }}</p>
        <div class="mt-4">
            <span class="bg-{{ $ins->estado == 'CONFIRMADA' ? 'green' : 'yellow' }}-100 text-{{ $ins->estado == 'CONFIRMADA' ? 'green' : 'yellow' }}-700 px-2 py-1 rounded text-sm font-bold">
                {{ $ins->estado == 'CONFIRMADA' ? 'Inscripción Confirmada' : 'Pendiente de Pago' }}
            </span>
        </div>
    </div>
    @empty
    <p class="text-gray-500">No estás inscrito en ningún curso todavía.</p>
    @endforelse
</div>
@endsection