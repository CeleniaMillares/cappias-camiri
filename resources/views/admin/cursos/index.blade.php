@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Cursos</h1>
        <p class="text-gray-500 text-sm mt-1">Administra los cursos, horarios e instructores.</p>
    </div>
    
    <!-- Solo la Directiva puede crear cursos -->
    @if(Auth::user()->hasRole('Directiva'))
    <a href="/admin/cursos/crear" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
        <i class="fas fa-plus"></i> Nuevo Curso
    </a>
    @endif
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Curso</th>
                <th class="p-4">Instructor</th>
                <th class="p-4">Costo</th>
                <th class="p-4">Horarios</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($cursos as $curso)
            <tr class="hover:bg-gray-50 transition">
                <!-- Datos del Curso -->
                <td class="p-4">
                    <p class="font-bold text-gray-800">{{ $curso->nombre }}</p>
                    @if($curso->imagen_url)
                        <span class="text-xs text-green-600"><i class="fas fa-image"></i> Con publicidad</span>
                    @endif
                </td>
                
                <td class="p-4">{{ $curso->instructor->nombre ?? 'N/A' }}</td>
                
                <td class="p-4 font-semibold">Bs. {{ number_format($curso->costo, 2) }}</td>
                
                <!-- Horarios -->
                <td class="p-4">
                    @forelse($curso->horarios as $horario)
                        <span class="bg-gray-100 px-2 py-1 text-xs rounded inline-block mb-1 border border-gray-200">
                            {{ $horario->dia_semana }} ({{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }})
                        </span>
                    @empty
                        <span class="text-red-500 text-xs">Sin horarios</span>
                    @endforelse
                </td>

                <!-- Acciones -->
                <td class="p-4 flex flex-wrap gap-3 items-center">
                    <!-- Botón para ver la lista de alumnos inscritos (Para Directiva y Socio) -->
                    <a href="/admin/cursos/{{ $curso->id_curso }}/alumnos" class="text-indigo-600 hover:underline font-semibold">
                        <i class="fas fa-users"></i> Alumnos
                    </a>

                    <!-- Solo la Directiva puede inscribir alumnos -->
                    @if(Auth::user()->hasRole('Directiva') && $curso->horarios->isNotEmpty())
                    <a href="/admin/alumnos/inscribir?id_curso={{ urlencode($curso->id_curso) }}" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500">
                        <i class="fas fa-user-plus" aria-hidden="true"></i> Inscribir
                    </a>
                    @endif

                    <!-- Botones exclusivos de la Directiva -->
                    @if(Auth::user()->hasRole('Directiva'))
                    <a href="/admin/cursos/{{ $curso->id_curso }}/editar" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500">
                        <i class="fas fa-edit" aria-hidden="true"></i> Editar
                    </a>
                    
                    <form method="POST" action="/admin/cursos/{{ $curso->id_curso }}" onsubmit="return confirm('¿Desactivar este curso? Ya no aparecerá para nuevas inscripciones.');">
                        @csrf 
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-red-700"><i class="fas fa-ban" aria-hidden="true"></i> Desactivar</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-500">
                    <i class="fas fa-chalkboard-teacher text-4xl text-gray-300 mb-3 block"></i>
                    No hay cursos registrados en el sistema.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection