@extends('layouts.app')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Alumnos</h1>
        <p class="text-gray-500 text-sm mt-1">Consulta alumnos, inscripciones y pagos pendientes.</p>
    </div>
    <a href="/admin/alumnos/inscribir" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">
        <i class="fas fa-user-plus mr-1"></i> Inscribir Nuevo Alumno
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
@endif

<form method="GET" action="/admin/alumnos" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[220px]">
        <label for="buscar" class="block text-sm font-semibold text-gray-700 mb-1">Buscar por nombre o CI</label>
        <input id="buscar" type="text" name="buscar" value="{{ $buscar }}" class="w-full border-gray-300 rounded-lg p-2 border" placeholder="Nombre o CI">
    </div>
    <button class="bg-slate-800 text-white px-5 py-2 rounded-lg font-semibold">Buscar</button>
    <a href="/admin/alumnos" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold">Limpiar</a>
</form>

<section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-4 border-b">
        <h2 class="text-lg font-bold text-gray-800">Alumnos</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-4">Nombre</th>
                    <th class="p-4">CI</th>
                    <th class="p-4">Teléfono</th>
                    <th class="p-4">Edad</th>
                    <th class="p-4">Inscripciones</th>
                    <th class="p-4">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($alumnos as $alumno)
                    <tr>
                        <td class="p-4 font-semibold text-gray-800">{{ $alumno->nombres }}</td>
                        <td class="p-4">{{ $alumno->ci }}</td>
                        <td class="p-4">{{ $alumno->telefono }}</td>
                        <td class="p-4">{{ $alumno->edad }}</td>
                        <td class="p-4">{{ $alumno->inscripciones->count() }}</td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-2">
                                <a href="/admin/alumnos/{{ $alumno->id_alumno }}/editar" class="text-blue-700 hover:underline">Editar</a>
                                <a href="/admin/alumnos/{{ $alumno->id_alumno }}/historial" class="text-slate-700 hover:underline">Historial</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-500">No se encontraron alumnos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b">
        <h2 class="text-lg font-bold text-gray-800">Inscripciones Pendientes de Pago</h2>
        <p class="text-sm text-gray-500">Estas inscripciones reservan un cupo hasta verificar el pago.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-4">Alumno</th>
                    <th class="p-4">Curso</th>
                    <th class="p-4">Método y pago</th>
                    @if(Auth::user()->hasRole('Directiva'))
                        <th class="p-4">Acción</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($inscripcionesPendientes as $inscripcion)
                    <tr>
                        <td class="p-4">
                            <span class="font-semibold">{{ $inscripcion->alumno->nombres }}</span>
                            <span class="block text-xs text-gray-500">CI: {{ $inscripcion->alumno->ci }}</span>
                        </td>
                        <td class="p-4">{{ $inscripcion->horarioCurso->curso->nombre }}</td>
                        <td class="p-4">
                            @forelse($inscripcion->pagos as $pago)
                                <div>{{ $pago->metodo }} — Bs. {{ number_format($pago->monto, 2) }}</div>
                            @empty
                                <span class="text-gray-400">Sin pago registrado</span>
                            @endforelse
                        </td>
                        @if(Auth::user()->hasRole('Directiva'))
                            <td class="p-4">
                                <form method="POST" action="/admin/inscripciones/{{ $inscripcion->id_inscripcion }}/confirmar">
                                    @csrf
                                    <button class="bg-green-600 text-white px-3 py-2 rounded-lg text-xs font-bold">Confirmar Pago</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ Auth::user()->hasRole('Directiva') ? 4 : 3 }}" class="p-8 text-center text-gray-500">No hay inscripciones pendientes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
