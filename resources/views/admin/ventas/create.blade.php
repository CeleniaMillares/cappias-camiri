@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Registrar Nueva Venta</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mb-4 shadow-sm">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/admin/ventas" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        @csrf
        <label class="block mb-2 font-semibold text-gray-700">Origen de la Venta:</label>
        <select name="id_feria" class="w-full border-gray-300 p-2 mb-6 rounded-lg focus:ring-blue-500 focus:border-blue-500 border">
            <option value="">Sede CAPPIAS</option>
            @foreach($ferias as $feria)
                <option value="{{ $feria->id_feria }}">{{ $feria->nombre }}</option>
            @endforeach
        </select>

        <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-800">Productos</h3>
        <div id="productos-container" class="space-y-4 mb-4">
            <div class="producto-row grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Buscar Producto:</label>
                    <select name="productos[0][id_producto]" class="product-select w-full border-gray-300 border p-2 rounded-lg" required onchange="autocompletarDatos(this)">
                        <option value="" data-socio="" data-precio="0">Escriba el nombre...</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id_producto }}" data-socio="{{ $prod->socio->nombre ?? 'N/A' }}" data-precio="{{ $prod->precio }}">
                                {{ $prod->nombre }} (Stock: {{ $prod->stock_actual }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Socio:</label>
                    <input type="text" class="socio-input w-full border-gray-200 bg-gray-100 border p-2 rounded-lg text-gray-600" readonly placeholder="Socio">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Precio:</label>
                    <input type="text" class="precio-input w-full border-gray-200 bg-gray-100 border p-2 rounded-lg text-gray-600" readonly placeholder="Bs. 0">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cant.:</label>
                    <input type="number" name="productos[0][cantidad]" class="cantidad-input w-full border-gray-300 border p-2 rounded-lg" min="1" value="1" required oninput="calcularTotales()">
                </div>
                <div class="md:col-span-1 flex items-end gap-2">
                    <div class="w-full text-center font-bold text-gray-700 subtotal-display bg-white p-2 rounded border border-gray-200 text-sm">Bs. 0</div>
                    <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 p-2 hidden"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>

        <button type="button" onclick="agregarProducto()" class="text-blue-600 text-sm mb-6 hover:underline font-semibold bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
            <i class="fas fa-plus-circle mr-1"></i> Agregar otro producto
        </button>

        <h3 class="text-lg font-bold border-b pb-2 mb-4 mt-6 text-gray-800">Método de Pago</h3>
        <div class="mb-4">
            <select name="metodo_pago" id="metodo_pago" class="w-full border-gray-300 border p-2 rounded-lg" onchange="actualizarMetodoPago()" required>
                <option value="EFECTIVO">Efectivo</option>
                <option value="QR">Pago QR</option>
                <option value="MIXTO">Mixto (Efectivo + QR)</option>
            </select>
        </div>

        <div id="div_mixto" class="mb-6 hidden bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <label class="block mb-2 font-semibold text-yellow-800">¿Cuánto se pagó en Efectivo? (Bs):</label>
            <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo" value="0" class="w-full border-yellow-300 p-3 rounded-lg border text-lg font-bold" oninput="calcularTotales()">
            <p class="text-sm text-yellow-700 mt-2">Saldo por QR: <b id="saldo_qr_text">Bs. 0.00</b></p>
        </div>

        <!-- QR DINÁMICO -->
        <div id="div_qr" class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hidden">
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

        <div class="bg-gray-50 p-4 rounded-lg mb-6 flex justify-between items-center border border-gray-200">
            <span class="text-lg font-bold text-gray-700">TOTAL A PAGAR:</span>
            <span id="total-venta" class="text-2xl font-extrabold text-green-600">Bs. 0.00</span>
        </div>

        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 w-full font-bold shadow-sm mt-2">
            <i class="fas fa-save mr-2"></i> Registrar Venta
        </button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .select2-container .select2-selection--single { height: 42px !important; border: 1px solid #d1d5db !important; border-radius: 0.5rem !important; padding-top: 5px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
</style>

<script>
    function inicializarSelect2() {
        $('.product-select').select2({ placeholder: "Escriba el nombre...", allowClear: true });
    }

    function autocompletarDatos(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        const socio = option.getAttribute('data-socio') || '';
        const precio = option.getAttribute('data-precio') || 0;
        
        const fila = selectElement.closest('.producto-row');
        fila.querySelector('.socio-input').value = socio;
        fila.querySelector('.precio-input').value = 'Bs. ' + parseFloat(precio).toFixed(2);
        calcularTotales();
    }

    let contador = 1;
    function agregarProducto() {
        const container = document.getElementById('productos-container');
        const fila = document.querySelector('.producto-row').cloneNode(true);
        
        fila.querySelector('.product-select').name = `productos[${contador}][id_producto]`;
        fila.querySelector('.product-select').value = ''; 
        fila.querySelector('.socio-input').value = '';
        fila.querySelector('.precio-input').value = '';
        fila.querySelector('.cantidad-input').name = `productos[${contador}][cantidad]`;
        fila.querySelector('.cantidad-input').value = '1'; 
        fila.querySelector('.subtotal-display').textContent = 'Bs. 0';
        fila.querySelector('button[onclick="eliminarFila(this)"]').classList.remove('hidden');

        $(fila.querySelector('.product-select')).select2('destroy');
        container.appendChild(fila);
        
        inicializarSelect2();
        contador++;
        calcularTotales();
    }

    function eliminarFila(boton) {
        const fila = boton.closest('.producto-row');
        $(fila.querySelector('.product-select')).select2('destroy');
        fila.remove();
        calcularTotales();
    }

    function calcularTotales() {
        let totalVenta = 0;
        document.querySelectorAll('.producto-row').forEach(fila => {
            const select = fila.querySelector('.product-select');
            const cantidad = parseInt(fila.querySelector('.cantidad-input').value) || 0;
            const precio = parseFloat(select.options[select.selectedIndex]?.getAttribute('data-precio')) || 0;
            const subtotal = precio * cantidad;
            fila.querySelector('.subtotal-display').textContent = 'Bs. ' + subtotal.toFixed(2);
            totalVenta += subtotal;
        });
        
        document.getElementById('total-venta').textContent = 'Bs. ' + totalVenta.toFixed(2);

        if (document.getElementById('metodo_pago').value === 'MIXTO') {
            const efectivo = parseFloat(document.getElementById('monto_efectivo').value) || 0;
            let saldo = totalVenta - efectivo;
            if (saldo < 0) saldo = 0;
            document.getElementById('saldo_qr_text').textContent = 'Bs. ' + (totalVenta === 0 ? '0.00' : saldo.toFixed(2));
        }
    }

    function actualizarMetodoPago() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('div_mixto').classList.toggle('hidden', metodo !== 'MIXTO');
        document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
        calcularTotales();
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

    $(document).ready(function() {
        inicializarSelect2();
        actualizarMetodoPago();
    });
</script>
@endsection