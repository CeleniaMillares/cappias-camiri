@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="/admin/socios" class="text-blue-600 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        <h1 class="text-2xl font-bold text-gray-800">Inscribir Nuevo Socio</h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="/admin/socios" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" autocomplete="off">
        @csrf
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="col-span-2">
                <label class="block mb-1 font-semibold">Nombre Completo:</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border p-2 rounded" required>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 font-semibold">Correo Electrónico:</label>
                <input type="email" name="correo" value="{{ old('correo') }}" class="w-full border p-2 rounded" required autocomplete="new-password">
            </div>
            <div>
                <label class="block mb-1 font-semibold">CI:</label>
                <input type="text" name="ci" value="{{ old('ci') }}" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Teléfono:</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full border p-2 rounded" required>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 font-semibold">Dirección (Opcional):</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full border p-2 rounded">
            </div>
            <div class="col-span-2">
                <label class="block mb-1 font-semibold">Contraseña Inicial:</label>
                <input type="password" name="contrasena" class="w-full border p-2 rounded" required autocomplete="new-password">
            </div>
            
            <div class="col-span-2 bg-green-50 border border-green-200 p-4 rounded-lg flex justify-between items-center mb-2">
                <div>
                    <p class="text-sm text-green-700 font-semibold uppercase">Matrícula</p>
                </div>
                <span class="text-2xl font-extrabold text-green-700">Bs. {{ number_format($montoInscripcion, 2) }}</span>
            </div>

            <div class="col-span-2 mb-2">
                <label class="block mb-2 font-semibold text-gray-700">Método de Pago:</label>
                <select name="metodo_pago" id="metodo_pago" class="w-full border p-2 rounded-lg" onchange="actualizarPago()" required>
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="QR">Pago QR</option>
                    <option value="MIXTO">Mixto</option>
                </select>
            </div>

            <!-- QR DINÁMICO -->
            <div id="div_qr" class="col-span-2 mb-4 bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hidden">
                <p class="font-bold text-blue-800 mb-2">Escanee el QR de CAPPIAS</p>
                
                @php
                    $qrSrc = $qrImage ? asset('storage/' . $qrImage) : null;
                @endphp

                @if($qrSrc)
                    <img id="qr_image" src="{{ $qrSrc }}" class="mx-auto w-40 h-40 object-contain border p-2 bg-white rounded shadow-sm" alt="QR CAPPIAS">
                @else
                    <div id="qr_placeholder" class="mx-auto w-40 h-40 bg-yellow-50 border-2 border-dashed border-yellow-300 rounded flex flex-col items-center justify-center text-yellow-700 p-3">
                        <i class="fas fa-qrcode text-3xl mb-2"></i>
                        <p class="text-[10px] font-semibold text-center">QR no configurado</p>
                        <p class="text-[9px] text-center mt-1">Contacte al Administrador</p>
                        <img id="qr_image" class="hidden mx-auto w-full h-full object-contain" alt="QR temporal">
                    </div>
                @endif
                
                <label class="flex items-center justify-center gap-2 mt-4 cursor-pointer">
                    <input type="checkbox" name="confirmar_qr" value="1" class="w-5 h-5 text-blue-600 rounded">
                    <span class="font-bold text-blue-800 text-sm">Confirmo que el pago ya fue recibido</span>
                </label>

                <div class="mt-4 pt-3 border-t border-blue-200">
                    <p class="text-xs text-red-600 font-semibold mb-2"><i class="fas fa-exclamation-triangle"></i> ¿El QR no funciona?</p>
                    <label for="qr_alternativo" class="cursor-pointer inline-block bg-white text-blue-600 border border-blue-300 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition text-xs font-semibold shadow-sm">
                        <i class="fas fa-upload mr-1"></i> Subir QR temporal
                    </label>
                    <input type="file" id="qr_alternativo" accept="image/*" class="hidden" onchange="cambiarQR(event)">
                </div>
            </div>

            <div id="div_mixto" class="col-span-2 mb-4 hidden bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <label class="block mb-1 font-semibold text-yellow-800">Monto en Efectivo (Bs):</label>
                <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo" value="{{ old('monto_efectivo', 0) }}" class="w-full border p-2 rounded-lg" oninput="calcularSaldo()">
                <p class="text-sm text-yellow-700 mt-1">Saldo por QR: <b id="saldo_text">Bs. 0.00</b></p>
            </div>
        </div>
        
        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 w-full font-bold mt-2">Inscribir Socio</button>
    </form>
</div>

<script>
    const montoInscripcion = {{ $montoInscripcion }};
    
    function actualizarPago() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
        document.getElementById('div_mixto').classList.toggle('hidden', metodo !== 'MIXTO');
        calcularSaldo();
    }

    function calcularSaldo() {
        const efectivo = parseFloat(document.getElementById('monto_efectivo').value) || 0;
        let saldo = montoInscripcion - efectivo;
        if (saldo < 0) saldo = 0;
        document.getElementById('saldo_text').textContent = 'Bs. ' + saldo.toFixed(2);
    }

    function cambiarQR(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const image = document.getElementById('qr_image');
                image.src = e.target.result;
                image.classList.remove('hidden');
                document.getElementById('qr_placeholder')?.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', actualizarPago);
</script>
@endsection