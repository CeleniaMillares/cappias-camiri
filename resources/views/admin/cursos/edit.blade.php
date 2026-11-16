@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6">
        <a href="/admin/cursos" class="mb-2 inline-flex items-center rounded-lg bg-gray-200 px-4 py-2 font-semibold text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Volver a la lista
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Curso</h1>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/admin/cursos/{{ $curso->id_curso }}" class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="id_usuario_instructor" class="mb-2 block font-semibold text-gray-700">Instructor:</label>
            <select id="id_usuario_instructor" name="id_usuario_instructor" class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring-blue-500" required>
                @foreach($instructores as $inst)
                    <option value="{{ $inst->id_usuario }}" @selected(old('id_usuario_instructor', $curso->id_usuario_instructor) == $inst->id_usuario)>{{ $inst->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="nombre" class="mb-2 block font-semibold text-gray-700">Nombre del Curso:</label>
                <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $curso->nombre) }}" placeholder="Ej: Taller de barro" class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="costo" class="mb-2 block font-semibold text-gray-700">Costo (Bs):</label>
                <input id="costo" type="number" min="0.01" step="0.01" name="costo" value="{{ old('costo', $curso->costo) }}" placeholder="Ej: 150.00" class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring-blue-500" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="mb-2 block font-semibold text-gray-700">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="5" class="w-full rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring-blue-500" placeholder="Describe el contenido y los objetivos del curso">{{ old('descripcion', $curso->descripcion) }}</textarea>
        </div>

        <div class="mb-6">
            <label for="imagen_url" class="mb-2 block font-semibold text-gray-700">Imagen/Afiche (opcional):</label>
            <input id="imagen_url" type="file" name="imagen_url" accept="image/*" class="w-full rounded-lg border bg-gray-50 p-2">
            <p class="mt-1 text-xs text-gray-500">Formatos de imagen, máximo 2 MB. Deja vacío para conservar la imagen actual.</p>
            @if($curso->imagen_url)
                <a href="{{ asset('storage/' . $curso->imagen_url) }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500">
                    <i class="fas fa-eye" aria-hidden="true"></i> Ver imagen actual
                </a>
            @endif
        </div>

        <div class="mb-4 flex items-center justify-between border-b pb-2">
            <h2 class="text-lg font-bold">Gestionar grupos</h2>
            <button type="button" data-add-group class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Agregar grupo
            </button>
        </div>
        <p class="mb-4 text-sm text-gray-500">Los grupos omitidos del formulario no se eliminan; los grupos con alumnos inscritos no pueden reducir su cupo por debajo de las inscripciones existentes.</p>
        <div data-group-container class="space-y-4">
            @foreach($horariosFormulario as $index => $horario)
                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 font-semibold">Grupo {{ $loop->iteration }}</legend>
                    <input type="hidden" name="horarios[{{ $index }}][id_horario_curso]" value="{{ $horario['id_horario_curso'] ?? '' }}">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="dia-{{ $index }}" class="mb-1 block text-sm">Días:</label>
                            <input id="dia-{{ $index }}" type="text" name="horarios[{{ $index }}][dia_semana]" value="{{ $horario['dia_semana'] ?? '' }}" placeholder="Lunes y Miércoles" class="w-full rounded border p-2" required>
                        </div>
                        <div>
                            <label for="cupo-{{ $index }}" class="mb-1 block text-sm">Cupo máximo:</label>
                            <input id="cupo-{{ $index }}" type="number" name="horarios[{{ $index }}][cupo_maximo]" value="{{ $horario['cupo_maximo'] ?? $cupoMax }}" min="{{ $cupoMin }}" max="{{ $cupoMax }}" class="w-full rounded border p-2" required>
                        </div>
                        <div>
                            <label for="inicio-hora-{{ $index }}" class="mb-1 block text-sm">Hora de inicio:</label>
                            <input id="inicio-hora-{{ $index }}" type="time" name="horarios[{{ $index }}][hora_inicio]" value="{{ isset($horario['hora_inicio']) ? \Carbon\Carbon::parse($horario['hora_inicio'])->format('H:i') : '' }}" class="w-full rounded border p-2" required>
                        </div>
                        <div>
                            <label for="fin-hora-{{ $index }}" class="mb-1 block text-sm">Hora de fin:</label>
                            <input id="fin-hora-{{ $index }}" type="time" name="horarios[{{ $index }}][hora_fin]" value="{{ isset($horario['hora_fin']) ? \Carbon\Carbon::parse($horario['hora_fin'])->format('H:i') : '' }}" class="w-full rounded border p-2" required>
                        </div>
                        <div>
                            <label for="inicio-fecha-{{ $index }}" class="mb-1 block text-sm">Fecha de inicio:</label>
                            <input id="inicio-fecha-{{ $index }}" type="date" name="horarios[{{ $index }}][fecha_inicio]" value="{{ $horario['fecha_inicio'] ?? '' }}" class="w-full rounded border p-2" required>
                        </div>
                        <div>
                            <label for="fin-fecha-{{ $index }}" class="mb-1 block text-sm">Fecha de fin:</label>
                            <input id="fin-fecha-{{ $index }}" type="date" name="horarios[{{ $index }}][fecha_fin]" value="{{ $horario['fecha_fin'] ?? '' }}" class="w-full rounded border p-2" required>
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <template id="group-template">
            <fieldset class="rounded-lg border border-gray-200 p-4">
                <legend class="px-2 font-semibold">Grupo __NUMBER__</legend>
                <input type="hidden" name="horarios[__INDEX__][id_horario_curso]" value="">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="dia-__INDEX__" class="mb-1 block text-sm">Días:</label>
                        <input id="dia-__INDEX__" type="text" name="horarios[__INDEX__][dia_semana]" placeholder="Lunes y Miércoles" class="w-full rounded border p-2" required>
                    </div>
                    <div>
                        <label for="cupo-__INDEX__" class="mb-1 block text-sm">Cupo máximo:</label>
                        <input id="cupo-__INDEX__" type="number" name="horarios[__INDEX__][cupo_maximo]" value="{{ $cupoMax }}" min="{{ $cupoMin }}" max="{{ $cupoMax }}" class="w-full rounded border p-2" required>
                    </div>
                    <div>
                        <label for="inicio-hora-__INDEX__" class="mb-1 block text-sm">Hora de inicio:</label>
                        <input id="inicio-hora-__INDEX__" type="time" name="horarios[__INDEX__][hora_inicio]" class="w-full rounded border p-2" required>
                    </div>
                    <div>
                        <label for="fin-hora-__INDEX__" class="mb-1 block text-sm">Hora de fin:</label>
                        <input id="fin-hora-__INDEX__" type="time" name="horarios[__INDEX__][hora_fin]" class="w-full rounded border p-2" required>
                    </div>
                    <div>
                        <label for="inicio-fecha-__INDEX__" class="mb-1 block text-sm">Fecha de inicio:</label>
                        <input id="inicio-fecha-__INDEX__" type="date" name="horarios[__INDEX__][fecha_inicio]" class="w-full rounded border p-2" required>
                    </div>
                    <div>
                        <label for="fin-fecha-__INDEX__" class="mb-1 block text-sm">Fecha de fin:</label>
                        <input id="fin-fecha-__INDEX__" type="date" name="horarios[__INDEX__][fecha_fin]" class="w-full rounded border p-2" required>
                    </div>
                </div>
            </fieldset>
        </template>

        <button type="submit" class="mt-6 w-full rounded-lg bg-green-600 px-4 py-2 font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500">
            <i class="fas fa-save mr-2" aria-hidden="true"></i> Actualizar Curso
        </button>
    </form>
</div>
<script>
    document.querySelector('[data-add-group]').addEventListener('click', function () {
        const container = document.querySelector('[data-group-container]');
        const index = container.querySelectorAll('fieldset').length;
        const markup = document.getElementById('group-template').innerHTML
            .replace(/__INDEX__/g, index)
            .replace(/__NUMBER__/g, index + 1);
        container.insertAdjacentHTML('beforeend', markup);
    });
</script>
@endsection
