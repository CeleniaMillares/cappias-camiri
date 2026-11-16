<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - CAPPIAS</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="/" class="font-bold text-xl text-slate-800"><img src="{{ asset('img/logo_cappias.png') }}" alt="CAPPIAS" class="h-10 w-auto"></a>
            <div class="flex gap-4">
                <a href="/vitrina/cursos" class="font-semibold text-gray-600 hover:text-green-600">Cursos</a>
                <a href="/vitrina/productos" class="font-semibold text-green-600">Productos</a>
                <button type="button" data-theme-toggle class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-700" aria-label="Activar modo oscuro" aria-pressed="false" title="Activar modo oscuro"><i class="fas fa-moon" aria-hidden="true"></i></button>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6 mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Catálogo de Productos</h1>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($productos as $prod)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 p-4 flex flex-col">
                @if($prod->fotografia_url)
                    <img src="{{ asset('storage/' . $prod->fotografia_url) }}" class="w-full h-40 object-cover rounded-lg mb-3">
                @endif
                <h2 class="font-bold text-lg">{{ $prod->nombre }}</h2>
                <p class="text-gray-500 text-sm">Socio: {{ $prod->socio->nombre ?? 'N/A' }}</p>
                <p class="text-xl font-bold text-green-600 mt-2">Bs. {{ number_format($prod->precio, 2) }}</p>
                <p class="text-xs text-gray-400 mb-3">Stock: {{ $prod->stock_actual }}</p>
                <p class="mt-auto text-sm font-semibold text-gray-600">Disponible para venta en ferias y sede</p>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>