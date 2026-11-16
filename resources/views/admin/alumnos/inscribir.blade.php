@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="/admin/alumnos" class="inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600"><i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Volver a Alumnos</a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-6">Inscribir Alumno</h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($cursos->isEmpty())
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 p-4 rounded-lg">No hay cursos activos con los que puedas inscribir alumnos.</div>
    @else
        <form method="POST" action="/admin/alumnos/inscribir" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            @csrf
            <div class="mb-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="id_curso" class="mb-1 block font-semibold">Curso</label>
                    <select id="id_curso" name="id_curso" class="w-full rounded-lg border p-2" required>
                        <option value="">Seleccione un curso</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id_curso }}" @selected($idCursoSeleccionado === $curso->id_curso)>{{ $curso->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="id_horario_curso" class="mb-1 block font-semibold">Grupo / Horario</label>
                    <select id="id_horario_curso" name="id_horario_curso" class="w-full rounded-lg border p-2" required>
                        <option value="">Seleccione un grupo</option>
                    @foreach($cursos as $curso)
                        @foreach($curso->horarios as $index => $horario)
                            <option value="{{ $horario->id_horario_curso }}" data-curso-id="{{ $curso->id_curso }}" @selected(old('id_horario_curso') === $horario->id_horario_curso)>
                                Grupo {{ $index + 1 }} — {{ $horario->dia_semana }}, {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }} (Bs. {{ number_format($curso->costo, 2) }})
                            </option>
                        @endforeach
                    @endforeach
                    </select>
                </div>
            </div>

            <h2 class="font-bold text-lg border-b pb-2 mb-4">Datos del Alumno</h2>
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold">Nombre completo</label>
                    <input name="nombres" value="{{ old('nombres') }}" class="w-full border p-2 rounded-lg" required maxlength="150">
                </div>
                <div>
                    <label class="block mb-1 font-semibold">CI</label>
                    <input name="ci" value="{{ old('ci') }}" class="w-full border p-2 rounded-lg" required minlength="6" maxlength="13" pattern="[0-9]{6,10}(-[A-Za-z0-9]{1,2})?">
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Edad</label>
                    <input type="number" name="edad" value="{{ old('edad') }}" min="1" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 font-semibold">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono') }}" class="w-full border p-2 rounded-lg" required inputmode="numeric" minlength="7" maxlength="8" pattern="[0-9]{7,8}">
                </div>
            </div>

            <h2 class="font-bold text-lg border-b pb-2 mb-4">Método de Pago</h2>
            <div class="mb-4">
                <select name="metodo_pago" id="metodo_pago" class="w-full border p-2 rounded-lg" onchange="actualizarPago()" required>
                    <option value="EFECTIVO" @selected(old('metodo_pago') === 'EFECTIVO')>Efectivo</option>
                    <option value="QR" @selected(old('metodo_pago') === 'QR')>QR</option>
                    <option value="MIXTO" @selected(old('metodo_pago') === 'MIXTO')>Mixto (Efectivo + QR)</option>
                </select>
            </div>
            <div id="div_mixto" class="hidden bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-4">
                <label class="block mb-1 font-semibold">Monto en efectivo (Bs.)</label>
                <input type="number" step="0.01" min="0" name="monto_efectivo" value="{{ old('monto_efectivo', 0) }}" class="w-full border p-2 rounded-lg">
            </div>
            <div id="div_qr" class="hidden bg-blue-50 border border-blue-200 p-4 rounded-lg mb-6 text-center">
                <p class="font-semibold text-blue-800 mb-2">QR oficial para el pago</p>
                @if($qrImage)
                    <img src="{{ asset('storage/'.$qrImage) }}" alt="QR oficial de pagos" class="mx-auto max-h-64 rounded-lg border p-2">
                @else
                    <div class="mx-auto w-40 h-40 bg-yellow-50 border-2 border-dashed border-yellow-300 rounded flex flex-col items-center justify-center text-yellow-700 p-3">
                        <i class="fas fa-qrcode text-3xl mb-2"></i>
                        <p class="text-[10px] font-semibold">QR no configurado</p>
                        <p class="text-[9px] mt-1">Contacte al Administrador</p>
                    </div>
                @endif
                <label class="flex items-center justify-center gap-2 mt-4 text-sm text-gray-700">
                    <input type="checkbox" name="confirmar_qr" value="1" @checked(old('confirmar_qr'))>
                    <span>Confirmo que el pago ya fue recibido</span>
                </label>
                <p class="text-xs text-blue-700 mt-2">El pago quedará pendiente de verificación por la Directiva.</p>
            </div>
            <button type="submit" class="w-full bg-green-600 px-4 py-2 rounded-lg font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500"><i class="fas fa-check mr-2" aria-hidden="true"></i> Registrar Inscripción</button>
        </form>
    @endif
</div>
<script>
    function actualizarGrupos() {
        const cursoId = document.getElementById('id_curso').value;
        const grupo = document.getElementById('id_horario_curso');
        const grupoSeleccionado = grupo.value;

        Array.from(grupo.options).forEach(function (option) {
            if (!option.dataset.cursoId) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const disponible = option.dataset.cursoId === cursoId;
            option.hidden = !disponible;
            option.disabled = !disponible;
        });

        if (grupo.selectedOptions.length && grupo.selectedOptions[0].dataset.cursoId !== cursoId) {
            grupo.value = '';
        } else if (grupoSeleccionado) {
            grupo.value = grupoSeleccionado;
        }
    }

    function actualizarPago() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('div_mixto').classList.toggle('hidden', metodo !== 'MIXTO');
        document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
    }

    const selectorCurso = document.getElementById('id_curso');
    if (selectorCurso) {
        selectorCurso.addEventListener('change', function () {
            document.getElementById('id_horario_curso').value = '';
            actualizarGrupos();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('metodo_pago')) {
            actualizarPago();
        }
        if (selectorCurso) {
            actualizarGrupos();
        }
    });
</script>
@endsection
