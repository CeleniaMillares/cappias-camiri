@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/socios" class="text-blue-600 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        <h1 class="text-2xl font-bold text-gray-800">Pago Mensual: {{ $socio->usuario->nombre }}</h1>
        <p class="text-gray-500 text-sm">Inscrito el: {{ \Carbon\Carbon::parse($socio->fecha_inscripcion)->format('d/m/Y') }}</p>
    </div>

    <!-- ESTADO ACTUAL -->
    <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-md font-bold text-gray-800 mb-3"><i class="fas fa-info-circle text-blue-500 mr-2"></i> Estado de Cuenta</h2>
        
        @if($saldoPendienteTotal > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-700 font-bold text-lg">Saldo pendiente: Bs. {{ number_format($saldoPendienteTotal, 2) }}</p>
                <p class="text-sm text-red-600 mt-1">
                    Debe {{ $mesesAdeudados }} mes(es). Primer mes: <strong>{{ ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $primerMesPendiente)->translatedFormat('F Y')) }}</strong>
                </p>
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-700 font-bold text-lg">¡Al día! 🎉</p>
                <p class="text-sm text-green-600 mt-1">
                    Puede pagar adelantado a partir de: <strong>{{ ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $primerMesPendiente)->translatedFormat('F Y')) }}</strong>
                </p>
            </div>
        @endif
    </div>

    <!-- TIMELINE -->
    <div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-md font-bold text-gray-800 mb-3"><i class="fas fa-calendar-alt text-indigo-500 mr-2"></i> Línea de Tiempo</h2>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
            @foreach($timeline as $mes)
                <div class="rounded-lg p-2 text-center
                    @if($mes['pagado']) bg-green-100 text-green-800 border-2 border-green-300
                    @elseif($mes['es_actual'] && !$mes['pagado']) bg-blue-100 text-blue-800 border-2 border-blue-300
                    @elseif($mes['es_futuro']) bg-gray-50 text-gray-500 border-2 border-gray-200
                    @else bg-red-100 text-red-800 border-2 border-red-300
                    @endif">
                    <p class="text-xs font-bold">{{ $mes['label'] }}</p>
                    <p class="text-[10px] mt-1">
                        @if($mes['pagado']) ✓ Pagado
                        @elseif($mes['es_actual']) ● Actual
                        @elseif($mes['es_futuro']) ○ Futuro
                        @else ✗ Debido
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- FORMULARIO -->
    <form method="POST" action="/admin/socios/{{ $socio->id_socio }}/pago-mensual" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        
        <h2 class="text-md font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-cash-register text-green-500 mr-2"></i> Registrar Pago</h2>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1 font-semibold text-sm">Mes de Inicio:</label>
                <input type="month" name="periodo_inicio" id="periodo_inicio" value="{{ old('periodo_inicio', $primerMesPendiente) }}" class="w-full border p-2 rounded" required onchange="actualizarPreview()">
            </div>
            <div>
                <label class="block mb-1 font-semibold text-sm">Cantidad de Meses:</label>
                {{-- 🔥 SIN max:12 --}}
                <input type="number" name="meses" id="meses" value="{{ old('meses', 1) }}" min="1" class="w-full border p-2 rounded" required oninput="actualizarPreview()">
            </div>
        </div>
        
        <!-- PREVIEW DINÁMICO -->
        <div id="preview" class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-3">
            <p class="text-xs font-bold text-indigo-800 mb-2"><i class="fas fa-list-check mr-1"></i> Meses que se registrarán:</p>
            <div id="preview-meses" class="flex flex-wrap gap-1"></div>
            <p id="preview-saltados" class="text-[10px] text-orange-600 mt-2 hidden"></p>
        </div>
        
        <div class="mb-4 bg-green-50 border border-green-200 p-3 rounded-lg flex justify-between items-center">
            <div>
                <p class="text-sm text-green-700 font-semibold">Mensualidad (Bs. {{ number_format($montoMensualidad, 2) }} c/u)</p>
            </div>
            <span id="total_display" class="text-2xl font-extrabold text-green-700">Bs. {{ number_format($montoMensualidad, 2) }}</span>
        </div>

        <div class="mb-4">
            <label class="block mb-2 font-semibold text-sm">Método de Pago:</label>
            <select name="metodo_pago" id="metodo_pago" class="w-full border p-2 rounded-lg" onchange="actualizarPago()" required>
                <option value="EFECTIVO">Efectivo (Pago 100% inmediato)</option>
                <option value="QR">Pago QR</option>
                <option value="MIXTO">Mixto (Efectivo + QR)</option>
            </select>
        </div>

        <!-- QR -->
        <div id="div_qr" class="mb-4 bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hidden">
            <p class="font-bold text-blue-800 mb-2 text-sm">Escanee el QR de CAPPIAS</p>
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
                <label for="qr_alternativo" class="cursor-pointer inline-block bg-white text-blue-600 border border-blue-300 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition text-xs font-semibold shadow-sm">
                    <i class="fas fa-upload mr-1"></i> Subir QR temporal
                </label>
                <input type="file" id="qr_alternativo" accept="image/*" class="hidden" onchange="cambiarQR(event)">
            </div>
        </div>

        <div id="div_mixto" class="mb-4 hidden bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <label class="block mb-1 font-semibold text-yellow-800 text-sm">Monto en Efectivo (Bs):</label>
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
    const mesesPagados = @json($mesesPagados);
    let totalPreview = 0;

    function actualizarPreview() {
        const inicio = document.getElementById('periodo_inicio').value;
        const meses = parseInt(document.getElementById('meses').value) || 1;
        
        if (!inicio) return;
        
        const [year, month] = inicio.split('-').map(Number);
        let cursor = new Date(year, month - 1, 1);
        
        const contenedor = document.getElementById('preview-meses');
        contenedor.innerHTML = '';
        
        let contador = 0;
        let saltados = 0;
        let safety = 0;
        let total = 0;
        
        while (contador < meses && safety < 60) {
            const periodo = cursor.getFullYear() + '-' + String(cursor.getMonth() + 1).padStart(2, '0');
            const label = cursor.toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
            
            if (mesesPagados.includes(periodo)) {
                saltados++;
            } else {
                const badge = document.createElement('span');
                badge.className = 'bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded';
                badge.textContent = label;
                contenedor.appendChild(badge);
                contador++;
                total += montoBase;
            }
            cursor.setMonth(cursor.getMonth() + 1);
            safety++;
        }
        
        totalPreview = total;
        document.getElementById('total_display').textContent = 'Bs. ' + totalPreview.toFixed(2);
        
        const saltadosEl = document.getElementById('preview-saltados');
        if (saltados > 0) {
            saltadosEl.textContent = '⚠ Se omitirán ' + saltados + ' mes(es) ya pagados.';
            saltadosEl.classList.remove('hidden');
        } else {
            saltadosEl.classList.add('hidden');
        }
        
        calcularSaldo();
    }

    function actualizarPago() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
        document.getElementById('div_mixto').classList.toggle('hidden', metodo !== 'MIXTO');
        calcularSaldo();
    }

    function calcularSaldo() {
        const efectivo = parseFloat(document.getElementById('monto_efectivo').value) || 0;
        let saldo = totalPreview - efectivo;
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

    document.addEventListener('DOMContentLoaded', function() {
        actualizarPago();
        actualizarPreview();
    });
</script>
@endsection