<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAPPIAS Camiri - Sistema de Gestión</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* 🔥 ESTILO PARA LA BARRA DE SCROLL (Fina y elegante) */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #475569; /* Color slate-600 */
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #64748b; /* Color slate-500 */
        }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden text-gray-800">

    <!-- Menú Lateral -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shadow-2xl z-20">
        <!-- Logo / Título -->
        <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950">
            <h1 class="text-xl font-bold tracking-wider text-white flex items-center gap-2">
                <i class="fas fa-leaf text-green-500"></i> CAPPIAS
            </h1>
        </div>

        <!-- Navegación con Scroll Personalizado -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
            
            <!-- 1. PRINCIPAL -->
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-2 tracking-wider">Principal</p>
            <a href="/admin/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/dashboard') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chart-pie w-5 text-center"></i> Dashboard
            </a>

            <!-- 2. OPERACIONES (Directiva) -->
            @if(Auth::user()->hasRole('Directiva'))
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-6 tracking-wider">Operaciones</p>
            
            <a href="/admin/cursos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/cursos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chalkboard-teacher w-5 text-center"></i> Cursos
            </a>
            <a href="/admin/inscripciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/inscripciones*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-graduate w-5 text-center"></i> Inscripciones
            </a>
            <a href="/admin/productos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/productos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-box-open w-5 text-center"></i> Productos
            </a>
            <a href="/admin/categorias" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/categorias*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-tags w-5 text-center"></i> Categorías
            </a>
            <a href="/admin/ferias" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/ferias*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-store w-5 text-center"></i> Ferias
            </a>
            <a href="/admin/socios" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/socios*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-id-card w-5 text-center"></i> Socios
            </a>

            <!-- 3. FINANZAS (Directiva) -->
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-6 tracking-wider">Finanzas</p>
            <a href="/admin/ventas" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/ventas*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-shopping-cart w-5 text-center"></i> Ventas
            </a>
            <a href="/admin/liquidaciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/liquidaciones*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-hand-holding-usd w-5 text-center"></i> Pagos a Socios
            </a>
            <a href="/admin/reportes" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/reportes*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chart-line w-5 text-center"></i> Reportes
            </a>
            @endif

            <!-- 4. MI ESPACIO (Socio/Instructor) -->
            @if(Auth::user()->hasRole('Socio/Instructor'))
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-6 tracking-wider">Mi Espacio</p>
            
            <a href="/admin/cursos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/cursos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chalkboard-teacher w-5 text-center"></i> Mis Cursos
            </a>
            <a href="/admin/inscripciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/inscripciones*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-graduate w-5 text-center"></i> Inscripciones
            </a>
            <a href="/admin/productos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/productos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-box-open w-5 text-center"></i> Mis Productos
            </a>
            <a href="/admin/ventas" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/ventas*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-shopping-cart w-5 text-center"></i> Mis Ventas
            </a>
            <a href="/admin/liquidaciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/liquidaciones*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-hand-holding-usd w-5 text-center"></i> Mis Pagos
            </a>
            <a href="/admin/mi-perfil-socio" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/mi-perfil-socio*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-circle w-5 text-center"></i> Mi Perfil
            </a>
            @endif

            <!-- 5. MI ESPACIO (Alumno) -->
            @if(Auth::user()->hasRole('Alumno'))
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-6 tracking-wider">Mi Espacio</p>
            
            <a href="/admin/inscripciones" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/inscripciones*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-graduate w-5 text-center"></i> Mis Inscripciones
            </a>
            <a href="/admin/mi-perfil" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/mi-perfil*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-circle w-5 text-center"></i> Mi Perfil
            </a>
            @endif

            <!-- 6. ADMINISTRACIÓN (Admin) -->
            @if(Auth::user()->hasRole('Administrador'))
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-6 tracking-wider">Sistema</p>
            
            <a href="/admin/usuarios" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/usuarios*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-users w-5 text-center"></i> Usuarios
            </a>
            <a href="/admin/parametros" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/parametros*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-cogs w-5 text-center"></i> Parámetros
            </a>
            <a href="/admin/auditoria" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/auditoria*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-history w-5 text-center"></i> Auditoría
            </a>
            @endif
        </nav>

        <!-- Cerrar Sesión (Fijo abajo) -->
        <div class="p-4 border-t border-slate-800 bg-slate-950 mt-auto">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 p-2.5 bg-red-600/10 text-red-500 border border-red-600/20 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-200 font-semibold shadow-sm">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Área de Contenido Principal -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Barra Superior -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 z-10 border-b border-gray-200">
            <div class="text-gray-500 font-medium">
                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-gray-800">{{ Auth::user()->nombre ?? 'Usuario' }}</p>
                    <p class="text-[11px] text-blue-600 font-bold uppercase tracking-wider">{{ Auth::user()->roles->first()->nombre ?? 'Sin Rol' }}</p>
                </div>
                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-blue-400 text-white rounded-full flex items-center justify-center font-bold text-lg shadow-md border-2 border-white">
                    {{ substr(Auth::user()->nombre ?? 'U', 0, 1) }}
                </div>
            </div>
        </header>

        <!-- Contenido Dinámico -->
        <main class="flex-1 p-8 overflow-y-auto bg-gray-50">
            @yield('content')
        </main>
    </div>

</body>
</html>