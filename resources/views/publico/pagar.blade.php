<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Venta - CAPPIAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <!-- Cabecera -->
        <div class="bg-blue-600 p-6 text-center text-white">
            <i class="fas fa-leaf text-4xl mb-2"></i>
            <h1 class="text-2xl font-bold">CAPPIAS Camiri</h1>
            <p class="text-blue-100 text-sm mt-1">Pago de Venta #{{ substr($venta->id_venta, 0, 8) }}</p>
        </div>

        <div class="p-6">
            <!-- Resumen de Pago -->
            <div class="bg-gray-50 p-4 rounded-xl mb-6 border border-gray-200 text-center">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total a Pagar</p>
                <p class="text-3xl font-extrabold text-gray-800">Bs. {{ number_format($venta->total, 2) }}</p>
                
                @if($totalPagado > 0)
                    <p class="text-sm text-green-600 font-bold mt-2">Ya abonado: Bs. {{ number_format($totalPagado, 2) }}</p>
                @endif
                <p class="text-lg font-bold text-red-600 mt-1">Saldo Pendiente: Bs. {{ number_format($saldo, 2) }}</p>
            </div>

            <!-- Imagen del QR -->
            <div class="text-center mb-6">
                <p class="font-bold text-gray-700 mb-2">Escanea el código QR para transferir</p>
                <img src="{{ asset('img/qr_cappias.png') }}" alt="QR" class="mx-auto w-48 h-48 object-contain border-2 border-gray-200 rounded-lg shadow-sm p-2 bg-white">
            </div>

            <!-- Mensajes de éxito -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm text-center font-semibold">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            <!-- Formulario del cliente -->
            @if($saldo > 0)
            <form action="/pagar/{{ $venta->id_venta }}" method="POST" enctype="multipart/form-data" class="border-t border-gray-200 pt-4">
                @csrf
                <h3 class="font-bold text-gray-800 mb-3"><i class="fas fa-upload text-blue-500 mr-2"></i>Sube tu comprobante aquí</h3>
                
                <label class="block text-sm font-semibold text-gray-600 mb-1">¿Cuánto transferiste? (Bs):</label>
                <input type="number" step="0.01" name="monto" value="{{ $saldo }}" class="w-full border p-3 rounded-lg mb-4 bg-gray-50 focus:bg-white transition" required>

                <label class="block text-sm font-semibold text-gray-600 mb-1">Foto del comprobante:</label>
                <input type="file" name="comprobante" accept="image/*" capture="environment" class="w-full border p-2 rounded-lg mb-4 bg-gray-50 text-sm" required>

                <button type="submit" class="bg-green-600 text-white w-full py-4 rounded-xl font-bold text-lg hover:bg-green-700 transition shadow-md">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Comprobante
                </button>
            </form>
            @else
            <div class="text-center bg-green-50 p-4 rounded-xl border border-green-200">
                <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                <p class="font-bold text-green-700">¡Esta venta ya está pagada en su totalidad!</p>
            </div>
            @endif
        </div>
    </div>

</body>
</html>