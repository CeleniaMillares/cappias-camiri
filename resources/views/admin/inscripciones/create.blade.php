@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Inscribir Alumno</h1>
    
    <!-- Tarjeta del Curso -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex gap-4 items-center">
        @if($horario->curso->imagen_url)
            <img src="{{ asset('storage/' . $horario->curso->imagen_url) }}" class="w-24 h-24 object-cover rounded-lg shadow-md border border-gray-200">
        @else
            <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400"><i class="fas fa-book fa-2x"></i></div>
        @endif
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $horario->curso->nombre }}</h2>
            <p class="text-gray-600 mt-1"><i class="far fa-clock mr-1"></i> {{ $horario->dia_semana }} ({{ $horario->hora_inicio }} - {{ $horario->hora_fin }})</p>
            <p class="text-green-600 font-bold mt-1 text-lg">Costo: Bs. {{ number_format($horario->curso->costo, 2) }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mb-4 shadow-sm">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/admin/inscripciones" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        <input type="hidden" name="id_horario_curso" value="{{ $horario->id_horario_curso }}">

        <h3 class="text-lg font-bold border-b pb-2 mb-4">Datos del Alumno</h3>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Nombre Completo:</label>
                <input type="text" name="nombres" value="{{ old('nombres') }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold text-gray-700">CI:</label>
                <input type="text" name="ci" value="{{ old('ci') }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold text-gray-700">Edad:</label>
                <input type="number" name="edad" value="{{ old('edad') }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 font-semibold text-gray-700">Teléfono:</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>
        </div>

        <h3 class="text-lg font-bold border-b pb-2 mb-4 mt-6">Método de Pago</h3>
        <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700">Seleccione el método:</label>
            <select name="metodo_pago" id="metodo_pago" class="w-full border-gray-300 border p-2 rounded-lg focus:ring-blue-500 focus:border-blue-500" onchange="actualizarPago()" required>
                <option value="EFECTIVO">Efectivo (presencial)</option>
                <option value="QR">Pago QR (Transferencia)</option>
                <option value="MIXTO">Pago Mixto (Efectivo + QR)</option>
            </select>
        </div>

        <div id="div_mixto" class="mb-4 hidden bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <label class="block mb-1 font-semibold text-yellow-800">Monto en Efectivo (Bs):</label>
            <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo" value="0" class="w-full border-yellow-300 p-2 rounded-lg focus:ring-yellow-500 focus:border-yellow-500" oninput="calcularSaldo()">
            <p class="text-sm text-yellow-700 mt-1">Saldo por QR: <b id="saldo_text">Bs. 0.00</b></p>
        </div>

        <div id="div_qr" class="mb-6 hidden bg-blue-50 p-4 rounded-lg border border-blue-200">
            @if($qrImage)
                <p class="font-semibold text-blue-800 mb-2">QR oficial para el pago:</p>
                <img src="{{ asset('storage/' . $qrImage) }}" alt="QR oficial de pagos" class="max-h-64 mx-auto rounded-lg border p-2">
            @else
                <p class="text-sm text-red-700">El QR oficial no está configurado.</p>
            @endif
            <p class="text-xs text-blue-700 mt-2">La inscripción quedará pendiente hasta que se verifique el pago.</p>
        </div>

        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 w-full font-bold shadow-sm">
            <i class="fas fa-save mr-2"></i> Registrar Inscripción
        </button>
    </form>
</div>

<script>
    const costoCurso = {{ $horario->curso->costo }};
    
    function actualizarPago() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('div_mixto').classList.toggle('hidden', metodo !== 'MIXTO');
        document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
        calcularSaldo();
    }

    function calcularSaldo() {
        const efectivo = parseFloat(document.getElementById('monto_efectivo').value) || 0;
        let saldo = costoCurso - efectivo;
        if (saldo < 0) saldo = 0;
        document.getElementById('saldo_text').textContent = 'Bs. ' + saldo.toFixed(2);
    }
</script>
@endsection