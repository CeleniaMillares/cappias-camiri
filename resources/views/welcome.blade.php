<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a CAPPIAS Camiri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Navbar Público -->
    <nav class="bg-white shadow-sm fixed w-full z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <i class="fas fa-leaf text-green-600 text-2xl"></i>
                    <span class="font-bold text-xl text-slate-800 tracking-wide">CAPPIAS Camiri</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/vitrina/cursos" class="text-gray-600 hover:text-green-600 font-semibold hidden md:block">Ver Cursos</a>
                    <a href="/vitrina/productos" class="text-gray-600 hover:text-green-600 font-semibold hidden md:block">Ver Productos</a>
                    <a href="/login" class="bg-slate-800 text-white px-5 py-2 rounded-lg hover:bg-slate-900 transition shadow-md font-semibold text-sm">
                        <i class="fas fa-sign-in-alt mr-2"></i> Acceso Personal
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="fixed top-20 left-1/2 z-30 w-[calc(100%-2rem)] max-w-3xl -translate-x-1/2 rounded-lg border border-green-300 bg-green-100 px-5 py-4 text-green-800 shadow-lg" role="status">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Hero Section -->
    <div class="relative bg-slate-900 h-screen flex items-center justify-center overflow-hidden pt-16">
        <!-- Imagen de fondo (Artesanía) -->
        <img src="https://images.unsplash.com/photo-1455390582262-044cdead2708?q=80&w=2000&auto=format&fit=crop" 
             class="absolute inset-0 w-full h-full object-cover opacity-30" alt="Artesanía">
        
        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                Impulsando el Talento y la Cultura de <span class="text-green-400">Camiri</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-200 mb-10">
                Descubre las artesanías hechas a mano por nuestros socios y aprende nuevas técnicas en nuestros cursos y talleres.
            </p>
            
            <!-- 🔥 BOTONES PARA VER SIN LOGIN -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/vitrina/cursos" class="bg-blue-600 text-white text-lg px-8 py-3 rounded-full hover:bg-blue-500 transition shadow-lg font-bold">
                    <i class="fas fa-chalkboard-teacher mr-2"></i> Explorar Cursos
                </a>
                <a href="/vitrina/productos" class="bg-green-600 text-white text-lg px-8 py-3 rounded-full hover:bg-green-500 transition shadow-lg font-bold">
                    <i class="fas fa-shopping-bag mr-2"></i> Ver Catálogo de Productos
                </a>
            </div>
        </div>
    </div>

    <!-- Sección de Características -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-800">¿Qué puedes hacer en CAPPIAS?</h2>
                <p class="text-gray-500 mt-2">Una plataforma diseñada para mostrar y potenciar el trabajo de nuestros artesanos.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-gray-50 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="fas fa-hands"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Compra Artesanías</h3>
                    <p class="text-gray-500">Explora el catálogo de productos hechos a mano por nuestros socios (cerámica, tejidos, madera).</p>
                </div>
                <!-- Card 2 -->
                <div class="bg-gray-50 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Inscríbete en Cursos</h3>
                    <p class="text-gray-500">Aprende nuevas técnicas inscribiéndote a los talleres que ofrecen nuestros instructores.</p>
                </div>
                <!-- Card 3 -->
                <div class="bg-gray-50 p-8 rounded-2xl text-center hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Entérate de Ferias</h3>
                    <p class="text-gray-500">Mantente al tanto de las próximas ferias donde CAPPIAS estará presente con lo mejor.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-gray-400 py-8 text-center">
        <p>&copy; {{ date('Y') }} CAPPIAS Camiri. Todos los derechos reservados.</p>
        <p class="text-sm mt-2">Sistema de Gestión desarrollado para el Taller de Grado.</p>
    </footer>

</body>
</html>