@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/ferias" class="mb-2 inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Feria</h1>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/admin/ferias/{{ $feria->id_feria }}">
            @csrf 
            @method('PUT') <!-- Importante para actualizar -->
            
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Nombre de la Feria:</label>
                <input type="text" name="nombre" value="{{ $feria->nombre }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">Fecha del Evento:</label>
                <input type="date" name="fecha" value="{{ \Carbon\Carbon::parse($feria->fecha)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            <div class="mb-8">
                <label class="block mb-2 font-semibold text-gray-700">Lugar:</label>
                <input type="text" name="lugar" value="{{ $feria->lugar }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" required>
            </div>

            @php
                $participantesActuales = $feria->socios->keyBy('id_socio');
                $sociosSeleccionados = old('gestionar_socios')
                    ? (old('socios') ?? [])
                    : $feria->socios->pluck('id_socio')->all();
            @endphp
            <input type="hidden" name="gestionar_socios" value="1">
            <section class="mb-8 rounded-lg border border-gray-200 p-4">
                <h2 class="mb-2 text-lg font-bold text-gray-800">Socios participantes</h2>
                <p class="mb-4 text-sm text-gray-600">Marca los socios que participarán y define si cada participación está pendiente o confirmada. Desmarcar un socio lo quitará de la feria.</p>

                @forelse($socios as $socio)
                    @php
                        $participacion = $participantesActuales->get($socio->id_socio);
                        $estadoSeleccionado = old(
                            'estados.' . $socio->id_socio,
                            $participacion?->pivot->estado ?? 'CONFIRMADO'
                        );
                    @endphp
                    <div class="mb-3 grid gap-3 rounded-lg bg-gray-50 p-3 sm:grid-cols-[1fr_auto] sm:items-center">
                        <label class="flex items-center gap-3 font-medium text-gray-700">
                            <input
                                type="checkbox"
                                name="socios[]"
                                value="{{ $socio->id_socio }}"
                                @checked(in_array($socio->id_socio, $sociosSeleccionados, true))
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                            >
                            <span>{{ $socio->usuario->nombre ?? 'Socio sin usuario' }} — CI {{ $socio->ci }}</span>
                        </label>
                        <select name="estados[{{ $socio->id_socio }}]" class="rounded-lg border-gray-300 p-2">
                            <option value="CONFIRMADO" @selected($estadoSeleccionado === 'CONFIRMADO')>Confirmado</option>
                            <option value="PENDIENTE" @selected($estadoSeleccionado === 'PENDIENTE')>Pendiente</option>
                        </select>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No hay socios registrados para asignar a esta feria.</p>
                @endforelse
            </section>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full flex justify-center items-center gap-2">
                <i class="fas fa-save text-lg"></i> Actualizar Feria
            </button>
        </form>
    </div>
</div>
@endsection