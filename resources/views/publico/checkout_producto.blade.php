<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
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
                {{ $errors->first() }}
                @if($errors->has('correo') && !auth()->check())
                    <a href="/login" class="underline font-bold ml-1">Inicia sesión aquí</a> para continuar.
                @endif
            </div>
        @endif

        <form method="POST" action="/checkout/producto/{{ $producto->id_producto }}">
            @csrf
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Correo Electrónico:</label>
                    @auth
                        <input type="email" name="correo" value="{{ Auth::user()->correo }}" class="w-full border p-2 rounded-lg bg-gray-100 text-gray-500" readonly>
                    @else
                        <input type="email" name="correo" value="{{ old('correo') }}" class="w-full border p-2 rounded-lg" required>
                    @endauth
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Nombre Completo:</label>
                    <input type="text" name="nombres" value="{{ old('nombres') }}" class="w-full border p-2 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Cantidad:</label>
                    <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="{{ $producto->stock_actual }}" class="w-full border p-2 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Método de Pago:</label>
                    <select name="metodo_pago" id="metodo_pago" class="w-full border p-2 rounded-lg" onchange="actualizarPago()" required>
                        <option value="EFECTIVO" @selected(old('metodo_pago', 'EFECTIVO') === 'EFECTIVO')>Efectivo (Pagar en sede)</option>
                        <option value="QR" @selected(old('metodo_pago') === 'QR')>Transferencia QR</option>
                    </select>
                </div>
                <div id="qr_pago" class="col-span-2 hidden mt-4 bg-blue-50 p-4 rounded-lg border border-blue-200 text-center">
                    <p class="font-semibold text-blue-800 mb-2">Escanea el QR oficial para realizar la transferencia:</p>
                    @php
                        $qrSrc = $qrImage ? asset('storage/' . $qrImage) : null;
                    @endphp
                    @if($qrSrc)
                        <img src="{{ $qrSrc }}" alt="QR oficial de pagos" class="mx-auto max-h-64 rounded-lg border p-2">
                    @else
                        <div class="mx-auto w-40 h-40 bg-yellow-50 border-2 border-dashed border-yellow-300 rounded flex flex-col items-center justify-center text-yellow-700 p-3">
                            <i class="fas fa-qrcode text-3xl mb-2"></i>
                            <p class="text-[10px] font-semibold text-center">QR no configurado</p>
                            <p class="text-[9px] text-center mt-1">Contacte al Administrador</p>
                        </div>
                    @endif
                </div>
                <div id="div_confirmar_qr" class="col-span-2 mb-4 hidden bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="confirmar_qr" value="1" class="w-5 h-5 text-blue-600 rounded" @checked(old('confirmar_qr'))>
                        <span class="font-bold text-blue-800 text-sm">Confirmo que el pago QR ya fue recibido</span>
                    </label>
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
            document.getElementById('qr_pago').classList.toggle('hidden', metodo !== 'QR');
            document.getElementById('div_confirmar_qr').classList.toggle('hidden', metodo !== 'QR');
        }

        document.addEventListener('DOMContentLoaded', actualizarPago);
    </script>
</body>
</html>