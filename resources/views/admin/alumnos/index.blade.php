@extends('layouts.app')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Alumnos</h1>
        <p class="text-gray-500 text-sm mt-1">Consulta alumnos, inscripciones y pagos pendientes.</p>
    </div>
    @if(Auth::user()->hasRole('Directiva'))
        <a href="/admin/alumnos/inscribir" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">
            <i class="fas fa-user-plus mr-1"></i> Inscribir Nuevo Alumno
        </a>
    @endif
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
    <button type="submit" class="bg-blue-600 px-4 py-2 rounded-lg font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500"><i class="fas fa-search mr-2" aria-hidden="true"></i> Buscar</button>
    <a href="/admin/alumnos" class="inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600"><i class="fas fa-times mr-2" aria-hidden="true"></i> Limpiar</a>
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
                                @if(Auth::user()->hasRole('Directiva'))
                                    <a href="/admin/alumnos/{{ $alumno->id_alumno }}/editar" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500"><i class="fas fa-edit" aria-hidden="true"></i> Editar</a>
                                @endif
                                <a href="/admin/alumnos/{{ $alumno->id_alumno }}/historial" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500"><i class="fas fa-eye" aria-hidden="true"></i> Historial</a>
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
                                <div class="mb-1">
                                    {{ $pago->metodo }} — Bs. {{ number_format($pago->monto, 2) }}
                                    @if($pago->comprobante_url)
                                        <a href="{{ asset('storage/' . $pago->comprobante_url) }}" target="_blank" rel="noopener noreferrer" class="ml-1 inline-flex items-center gap-1 rounded bg-blue-600 px-2 py-1 text-xs font-semibold text-white hover:bg-blue-700">
                                            <i class="fas fa-image" aria-hidden="true"></i> Ver comprobante
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <span class="text-gray-400">Sin pago registrado</span>
                            @endforelse
                        </td>
                        @if(Auth::user()->hasRole('Directiva'))
                            <td class="p-4">
                                <form method="POST" action="/admin/inscripciones/{{ $inscripcion->id_inscripcion }}/confirmar">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500"><i class="fas fa-check mr-2" aria-hidden="true"></i> Confirmar Pago</button>
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
