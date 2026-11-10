<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - CAPPIAS Camiri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="/" class="font-bold text-xl text-slate-800"><i class="fas fa-leaf text-green-500 mr-2"></i>CAPPIAS</a>
            <div class="flex gap-4">
                <a href="/vitrina/cursos" class="font-semibold text-green-600">Cursos</a>
                <a href="/vitrina/productos" class="font-semibold text-gray-600 hover:text-green-600">Productos</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6 mt-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Nuestros Cursos</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($cursos as $curso)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                @if($curso->imagen_url)
                    <img src="{{ asset('storage/' . $curso->imagen_url) }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400"><i class="fas fa-book fa-3x"></i></div>
                @endif
                <div class="p-4">
                    <h2 class="font-bold text-xl mb-2">{{ $curso->nombre }}</h2>
                    <p class="text-gray-500 text-sm mb-4">Instructor: {{ $curso->instructor->nombre ?? 'N/A' }}</p>
                    <p class="text-2xl font-bold text-green-600 mb-4">Bs. {{ number_format($curso->costo, 2) }}</p>
                    <a href="/checkout/curso/{{ $curso->id_curso }}" class="block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Inscribirme</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>