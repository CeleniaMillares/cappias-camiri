<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAPPIAS Camiri - Sistema de Gestión</title>
    @include('partials.theme-head')
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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
<body class="flex h-screen overflow-hidden bg-gray-100 text-gray-800">

    <div id="sidebar-backdrop" class="fixed inset-0 z-20 hidden bg-slate-950/60 md:hidden" aria-hidden="true"></div>
    <aside id="app-sidebar" aria-hidden="true" class="fixed inset-y-0 left-0 z-30 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 shadow-2xl transition-transform duration-200 md:static md:translate-x-0">
        <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950">
            <a href="/admin/dashboard" class="inline-flex items-center rounded-md bg-white px-2 py-1" aria-label="CAPPIAS Camiri, panel principal">
                <img src="{{ asset('img/logo_cappias.png') }}" alt="CAPPIAS" class="h-10 w-auto object-contain">
            </a>
            <button type="button" id="sidebar-close" class="absolute right-3 inline-flex h-9 w-9 items-center justify-center rounded-lg p-2 text-slate-300 hover:bg-slate-800 md:hidden" aria-label="Cerrar menú" title="Cerrar menú">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Navegación con Scroll Personalizado -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
            
            <!-- 1. PRINCIPAL -->
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-2 tracking-wider">Principal</p>
            <a href="/admin/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/dashboard') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chart-pie w-5 text-center"></i> Panel de Control
            </a>

            <!-- 2. OPERACIONES (Directiva) -->
            @if(Auth::user()->hasRole('Directiva'))
            <p class="text-[10px] text-slate-500 uppercase font-bold px-3 mb-2 mt-6 tracking-wider">Operaciones</p>
            
            <a href="/admin/cursos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/cursos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-chalkboard-teacher w-5 text-center"></i> Cursos
            </a>
            <a href="/admin/alumnos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/alumnos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-graduate w-5 text-center"></i> Alumnos
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
            <a href="/admin/pagos-socios" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/pagos-socios*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
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
            <a href="/admin/alumnos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/alumnos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-user-graduate w-5 text-center"></i> Alumnos
            </a>
            <a href="/admin/productos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/productos*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-box-open w-5 text-center"></i> Mis Productos
            </a>
            <a href="/admin/ventas" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/ventas*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-shopping-cart w-5 text-center"></i> Mis Ventas
            </a>
            <a href="/admin/pagos-socios" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/pagos-socios*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-hand-holding-usd w-5 text-center"></i> Mis Pagos
            </a>
            <a href="/admin/mi-perfil-socio" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/mi-perfil-socio*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
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
            <a href="/admin/backups" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('admin/backups*') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="fas fa-database w-5 text-center"></i> Respaldos
            </a>
            @endif
        </nav>

        <!-- Cerrar Sesión (Fijo abajo) -->
        <div class="p-4 border-t border-slate-800 bg-slate-950 mt-auto">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-red-700">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Área de Contenido Principal -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Barra Superior -->
        <header class="z-10 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" id="sidebar-open" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden" aria-label="Abrir menú" title="Abrir menú" aria-controls="app-sidebar" aria-expanded="false">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
                <div class="truncate text-sm font-medium text-gray-500 sm:text-base">
                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                <button type="button" data-theme-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-600 transition hover:bg-gray-50" aria-label="Activar modo oscuro" aria-pressed="false" title="Activar modo oscuro">
                    <i class="fas fa-moon" aria-hidden="true"></i>
                </button>
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
        <main class="min-w-0 flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-8">
            @yield('content')
        </main>
    </div>

<script>
    (function () {
        const sidebar = document.getElementById('app-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openButton = document.getElementById('sidebar-open');
        const closeButton = document.getElementById('sidebar-close');

        function setSidebarOpen(isOpen) {
            const isMobile = window.matchMedia('(max-width: 767px)').matches;
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            backdrop.classList.toggle('hidden', !isOpen);
            openButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            sidebar.setAttribute('aria-hidden', isMobile && !isOpen ? 'true' : 'false');
            sidebar.inert = isMobile && !isOpen;
        }

        openButton.addEventListener('click', function () {
            setSidebarOpen(true);
        });
        closeButton.addEventListener('click', function () {
            setSidebarOpen(false);
        });
        backdrop.addEventListener('click', function () {
            setSidebarOpen(false);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setSidebarOpen(false);
            }
        });
        window.addEventListener('resize', function () {
            setSidebarOpen(false);
        });
        sidebar.querySelectorAll('nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 767px)').matches) {
                    setSidebarOpen(false);
                }
            });
        });
        setSidebarOpen(false);
    })();
</script>
</body>
</html>