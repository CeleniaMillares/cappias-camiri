@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mi Perfil</h1>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-2"><i class="fas fa-user text-blue-500 mr-2"></i> {{ $user->nombre }}</h2>
        <p class="text-gray-600">{{ $user->correo }}</p>
    </div>

    <!-- Mis Inscripciones -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-graduation-cap text-indigo-500 mr-2"></i> Mis Cursos Inscritos</h2>
        @forelse($inscripciones as $ins)
            <div class="border border-gray-100 rounded-lg p-4 mb-3 flex justify-between items-center bg-gray-50">
                <div>
                    <p class="font-bold text-gray-800">{{ $ins->horarioCurso->curso->nombre }}</p>
                    <p class="text-sm text-gray-500">{{ $ins->horarioCurso->dia_semana }} ({{ $ins->horarioCurso->hora_inicio }} - {{ $ins->horarioCurso->hora_fin }})</p>
                </div>
                <div>
                    @if($ins->estado === 'CONFIRMADA')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Pagado</span>
                    @else
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">Pendiente</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-center py-4">No estás inscrito en ningún curso todavía.</p>
        @endforelse
    </div>
</div>
@endsection