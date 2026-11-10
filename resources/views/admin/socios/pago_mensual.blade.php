@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="/admin/socios" class="text-blue-600 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        <h1 class="text-2xl font-bold text-gray-800">Pago Mensual: {{ $socio->usuario->nombre }}</h1>
    </div>

    <form method="POST" action="/admin/socios/{{ $socio->id_socio }}/pago-mensual" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1 font-semibold">Mes de Inicio:</label>
                <input type="month" name="periodo_inicio" value="{{ old('periodo_inicio', now()->format('Y-m')) }}" class="w-full border p-2 rounded" required onchange="calcularTotal()">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Cantidad de Meses:</label>
                <input type="number" name="meses" id="meses" value="{{ old('meses', 1) }}" min="1" max="12" class="w-full border p-2 rounded" required oninput="calcularTotal()">
            </div>
        </div>
        
        <div class="mb-4 bg-green-50 border border-green-200 p-4 rounded-lg flex justify-between items-center">
            <div>
                <p class="text-sm text-green-700 font-semibold">Mensualidad (Base: Bs. {{ number_format($montoMensualidad, 2) }})</p>
                <p class="text-xs text-green-600">Total calculado:</p>
            </div>
            <span id="total_display" class="text-2xl font-extrabold text-green-700">Bs. {{ number_format($montoMensualidad, 2) }}</span>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700">Método de Pago:</label>
            <select name="metodo_pago" id="metodo_pago" class="w-full border p-2 rounded-lg" onchange="actualizarPago()" required>
                <option value="EFECTIVO">Efectivo</option>
                <option value="QR">Pago QR</option>
                <option value="MIXTO">Mixto</option>
            </select>
        </div>

        <!-- QR DINÁMICO -->
        <div id="div_qr" class="mb-4 bg-blue-50 p-4 rounded-lg border border-blue-200 text-center">
            <p class="font-bold text-blue-800 mb-2">Escanee el QR de CAPPIAS</p>
            
            @php
                $defaultQr = asset('img/qr_cappias.png');
                $qrSrc = (isset($qrImage) && $qrImage) ? asset('storage/' . $qrImage) : $defaultQr;
            @endphp

            <img id="qr_image" src="{{ $qrSrc }}" class="mx-auto w-40 h-40 object-contain border p-2 bg-white rounded shadow-sm" alt="QR CAPPIAS">
            
            <label class="flex items-center justify-center gap-2 mt-4 cursor-pointer">
                <input type="checkbox" name="confirmar_qr" value="1" class="w-5 h-5 text-blue-600 rounded">
                <span class="font-bold text-blue-800 text-sm">Confirmo que el pago QR ya fue recibido</span>
            </label>

            <div class="mt-4 pt-3 border-t border-blue-200">
                <p class="text-xs text-red-600 font-semibold mb-2"><i class="fas fa-exclamation-triangle"></i> ¿El QR no funciona?</p>
                <label for="qr_alternativo" class="cursor-pointer inline-block bg-white text-blue-600 border border-blue-300 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition text-xs font-semibold shadow-sm">
                    <i class="fas fa-upload mr-1"></i> Subir QR temporal
                </label>
                <input type="file" id="qr_alternativo" accept="image/*" class="hidden" onchange="cambiarQR(event)">
            </div>
        </div>

        <div id="div_mixto" class="mb-6 hidden bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <label class="block mb-1 font-semibold text-yellow-800">Monto en Efectivo (Bs):</label>
            <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo" value="{{ old('monto_efectivo', 0) }}" class="w-full border p-2 rounded-lg" oninput="calcularSaldo()">
            <p class="text-sm text-yellow-700 mt-1">Saldo por QR: <b id="saldo_text">Bs. 0.00</b></p>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 w-full font-bold">
            <i class="fas fa-save mr-2"></i> Registrar Pago
        </button>
    </form>
</div>

<script>
    const montoBase = {{ $montoMensualidad }};
    
    function calcularTotal() {
        const meses = parseInt(document.getElementById('meses').value) || 1;
        const total = montoBase * meses;
        document.getElementById('total_display').textContent = 'Bs. ' + total.toFixed(2);
        calcularSaldo();
    }

    function actualizarPago() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
        document.getElementById('div_mixto').classList.toggle('hidden', metodo !== 'MIXTO');
        calcularSaldo();
    }

    function calcularSaldo() {
        const meses = parseInt(document.getElementById('meses').value) || 1;
        const total = montoBase * meses;
        const efectivo = parseFloat(document.getElementById('monto_efectivo').value) || 0;
        let saldo = total - efectivo;
        if (saldo < 0) saldo = 0;
        document.getElementById('saldo_text').textContent = 'Bs. ' + saldo.toFixed(2);
    }

    function cambiarQR(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('qr_image').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', actualizarPago);
</script>
@endsection