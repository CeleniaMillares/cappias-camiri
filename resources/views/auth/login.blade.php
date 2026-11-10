<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CAPPIAS Camiri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-2xl flex w-full max-w-4xl mx-auto overflow-hidden m-4">
        
        <!-- Lado Izquierdo: Imagen y Branding -->
        <div class="hidden md:flex md:w-1/2 bg-slate-900 relative items-center justify-center p-12 overflow-hidden">
            <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?q=80&w=1000&auto=format&fit=crop" 
                 class="absolute inset-0 w-full h-full object-cover opacity-20" alt="Cerámica">
            <div class="relative z-10 text-center">
                <i class="fas fa-leaf text-green-400 text-6xl mb-6"></i>
                <h2 class="text-3xl font-bold text-white mb-4">CAPPIAS Camiri</h2>
                <p class="text-gray-300">Portal de gestión para artesanos, instructores y alumnos. Impulsando el talento local.</p>
            </div>
        </div>

        <!-- Lado Derecho: Formulario -->
        <div class="w-full md:w-1/2 p-8 md:p-12">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800">Bienvenido</h1>
                <p class="text-gray-500 mt-2">Ingresa tus credenciales para acceder.</p>
            </div>

            <!-- Errores -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf
                
                <!-- Correo -->
                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="correo" value="{{ old('correo') }}" required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                            placeholder="admin@cappias.com">
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Contraseña</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="contrasena" required
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <!-- Botón -->
                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 transition shadow-lg flex justify-center items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="/" class="text-sm text-gray-500 hover:text-green-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Volver a la página de inicio
                </a>
            </div>
        </div>
    </div>

</body>
</html>