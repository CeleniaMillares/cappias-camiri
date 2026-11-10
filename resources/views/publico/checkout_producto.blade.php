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
            </div>
        @endif

        <form method="POST" action="/checkout/producto/{{ $producto->id_producto }}">
            @csrf
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Correo Electrónico:</label>
                    @auth
                        <input type="email" name="correo" value="{{ Auth::user()->correo }}" class="w-full border p-2 rounded-lg bg-gray-100 text-gray-500" readonly>
                    @else
                        <input type="email" name="correo" class="w-full border p-2 rounded-lg" required>
                    @endauth
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
                    <select name="metodo_pago" class="w-full border p-2 rounded-lg" required>
                        <option value="EFECTIVO">Efectivo (Pagar en sede)</option>
                        <option value="QR">Transferencia QR</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="bg-green-600 text-white w-full py-3 rounded-lg font-bold hover:bg-green-700 transition shadow-md">
                <i class="fas fa-shopping-cart mr-2"></i> Confirmar Compra
            </button>
        </form>
    </div>
</body>
</html>