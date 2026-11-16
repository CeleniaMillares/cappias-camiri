<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - CAPPIAS Camiri</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white p-4 shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between">
            <a href="/" class="text-xl font-bold text-slate-800"><img src="{{ asset('img/logo_cappias.png') }}" alt="CAPPIAS" class="h-10 w-auto"></a>
            <div class="flex gap-4">
                <a href="/vitrina/cursos" class="font-semibold text-green-600">Cursos</a>
                <a href="/vitrina/productos" class="font-semibold text-gray-600 hover:text-green-600">Productos</a>
                <button type="button" data-theme-toggle class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-700" aria-label="Activar modo oscuro" aria-pressed="false" title="Activar modo oscuro"><i class="fas fa-moon" aria-hidden="true"></i></button>
            </div>
        </div>
    </nav>

    <main class="mx-auto mt-8 max-w-7xl p-4 sm:p-6">
        <h1 class="mb-6 text-3xl font-bold text-gray-800">Nuestros Cursos</h1>
        @if($cursos->isEmpty())
            <p class="rounded-xl border border-gray-200 bg-white p-6 text-gray-600">No hay cursos disponibles en este momento.</p>
        @else
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                @foreach($cursos as $curso)
                    @php
                        $gruposDisponibles = $curso->horarios->filter(fn ($horario) =>
                            $horario->fecha_fin >= now()->toDateString()
                            && $horario->inscripciones_count < $horario->cupo_maximo
                        )->values();
                    @endphp
                    <article class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        @if($curso->imagen_url)
                            <a href="{{ asset('storage/' . $curso->imagen_url) }}" target="_blank" rel="noopener" aria-label="Ver afiche de {{ $curso->nombre }}">
                                <img src="{{ asset('storage/' . $curso->imagen_url) }}" alt="Afiche del curso {{ $curso->nombre }}" class="max-h-[36rem] w-full bg-gray-50 object-contain">
                            </a>
                        @else
                            <div class="flex h-64 w-full items-center justify-center bg-gray-100 text-gray-400"><i class="fas fa-book fa-3x" aria-hidden="true"></i></div>
                        @endif
                        <div class="p-5">
                            <h2 class="mb-2 text-2xl font-bold text-gray-800">{{ $curso->nombre }}</h2>
                            <p class="mb-4 text-sm text-gray-500">Instructor: {{ $curso->instructor->nombre ?? 'N/A' }}</p>
                            @if($curso->descripcion)
                                <p class="mb-5 whitespace-pre-line leading-relaxed text-gray-700">{{ $curso->descripcion }}</p>
                            @else
                                <p class="mb-5 text-gray-500">No hay una descripción disponible.</p>
                            @endif
                            <p class="mb-5 text-2xl font-bold text-green-600">Bs. {{ number_format($curso->costo, 2) }}</p>

                            <section aria-label="Grupos disponibles">
                                <h3 class="mb-3 text-lg font-bold text-gray-800">Grupos disponibles</h3>
                                @forelse($gruposDisponibles as $index => $horario)
                                    <div class="mb-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        <p class="font-semibold text-gray-800">Grupo {{ $index + 1 }}: {{ $horario->dia_semana }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}
                                            · {{ \Carbon\Carbon::parse($horario->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($horario->fecha_fin)->format('d/m/Y') }}
                                        </p>
                                        <p class="text-sm text-gray-600">Cupos disponibles: {{ max(0, $horario->cupo_maximo - $horario->inscripciones_count) }}</p>
                                    </div>
                                @empty
                                    <p class="rounded-lg bg-gray-50 p-3 text-sm text-gray-600">No hay grupos disponibles para inscripción actualmente.</p>
                                @endforelse
                            </section>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</body>
</html>
