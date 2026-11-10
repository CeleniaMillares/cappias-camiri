<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar - {{ $producto->nombre }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
        <a href="/vitrina/productos" class="text-blue-600 hover:underline text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
        <h1 class="text-2xl font-bold mb-2">Comprar Producto</h1>
        <p class="text-gray-500 mb-6">{{ $producto->nombre }} - Bs. {{ number_format($producto->precio, 2) }}</p>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/checkout/producto/{{ $producto->id_producto }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Correo Electrónico:</label>
                    <input type="email" name="correo" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Nombre Completo:</label>
                    <input type="text" name="nombres" class="w-full border p-2 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Cantidad:</label>
                    <input type="number" name="cantidad" value="1" min="1" max="{{ $producto->stock_actual }}" class="w-full border p-2 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Método de Pago:</label>
                    <select name="metodo_pago" id="metodo_pago" class="w-full border p-2 rounded-lg" onchange="actualizarPago()" required>
                        <option value="EFECTIVO">Efectivo (Pagar en sede)</option>
                        <option value="QR">Transferencia QR</option>
                    </select>
                </div>

                <!-- QR DINÁMICO PÚBLICO -->
                <div id="div_qr" class="col-span-2 mb-4 bg-blue-50 p-4 rounded-lg border border-blue-200 text-center hidden">
                    <p class="font-bold text-blue-800 mb-2">Escanee este QR para transferir</p>
                    
                    @php
                        $defaultQr = asset('img/qr_cappias.png');
                        $qrSrc = (isset($qrImage) && $qrImage) ? asset('storage/' . $qrImage) : $defaultQr;
                    @endphp

                    <img src="{{ $qrSrc }}" class="mx-auto w-40 h-40 object-contain border p-2 bg-white rounded shadow-sm" alt="QR CAPPIAS">
                    
                    <p class="text-xs text-blue-600 mt-2">Una vez transferido, la Directiva verificará tu pago para confirmar tu compra.</p>
                </div>
            </div>

            <button type="submit" class="bg-green-600 text-white w-full py-3 rounded-lg font-bold hover:bg-green-700 transition shadow-md">
                <i class="fas fa-shopping-cart mr-2"></i> Confirmar Compra
            </button>
        </form>
    </div>

    <script>
        function actualizarPago() {
            const metodo = document.getElementById('metodo_pago').value;
            document.getElementById('div_qr').classList.toggle('hidden', metodo === 'EFECTIVO');
        }
        document.addEventListener('DOMContentLoaded', actualizarPago);
    </script>
</body>
</html>