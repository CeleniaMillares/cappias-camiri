<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#064e3b">
    <title>Iniciar sesión | CAPPIAS Camiri</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        h1, h2 { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    @include('partials.theme-toggle')
    <main class="flex min-h-screen items-stretch">
        <aside class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-green-950 via-green-900 to-slate-900 p-12 text-white md:flex md:flex-col md:justify-between lg:p-16">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full border-[40px] border-white/5"></div>
            <div class="absolute -bottom-40 -left-20 h-[30rem] w-[30rem] rounded-full border-[50px] border-green-400/10"></div>

            <a href="/" class="relative z-10 inline-flex w-fit items-center gap-3 text-white">
                <span class="flex h-11 items-center justify-center rounded-xl bg-white p-1">
                    <img src="{{ asset('img/logo_cappias.png') }}" alt="CAPPIAS" class="h-10 w-auto object-contain">
                </span>
                <span class="font-display text-lg font-extrabold tracking-tight">Sistema de Gestión</span>
            </a>

            <div class="relative z-10 mx-auto w-full max-w-xl">
                <div class="inline-flex rounded-2xl bg-white p-3 shadow-2xl">
                    <img src="{{ asset('img/logo_cappias.png') }}" alt="Logo oficial de CAPPIAS Camiri" class="h-auto max-h-80 w-full max-w-sm object-contain">
                </div>
                <h1 class="mt-9 font-display text-4xl font-extrabold leading-tight lg:text-5xl">Sistema de Gestión <span class="text-green-300">CAPPIAS Camiri</span></h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-green-50/80">
                    Portal para acompañar el trabajo de nuestros asociados, instructores y alumnos.
                </p>
            </div>

            <p class="relative z-10 text-xs font-medium tracking-wide text-white/50">Cámara Provincial de la Pequeña Industria, Artesanías y Servicios</p>
        </aside>

        <section class="flex w-full items-center justify-center px-5 py-10 md:w-1/2 sm:px-8">
            <div class="w-full max-w-md">
                <a href="/" class="mb-8 inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 md:hidden">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al inicio
                </a>

                <div class="mb-8 text-center md:text-left">
                    <img src="{{ asset('img/logo_cappias.png') }}" alt="CAPPIAS Camiri" class="mx-auto mb-6 h-24 w-auto object-contain md:hidden">
                    <p class="text-sm font-bold uppercase tracking-[.16em] text-green-700">Acceso al sistema</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Bienvenido</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Ingresa tus credenciales para continuar.</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" role="status">
                        <i class="fa-solid fa-circle-check mt-0.5 text-green-600" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-600" aria-hidden="true"></i>
                        <div>
                            <p class="font-bold">No se pudo iniciar sesión</p>
                            <p class="mt-1">{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="/login" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/70 sm:p-8">
                    @csrf

                    <div>
                        <label for="correo" class="mb-2 block text-sm font-bold text-slate-700">Correo electrónico</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            </span>
                            <input id="correo" type="email" name="correo" value="{{ old('correo') }}" autocomplete="username" required autofocus
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100"
                                placeholder="nombre@correo.com">
                        </div>
                    </div>

                    <div>
                        <label for="contrasena" class="mb-2 block text-sm font-bold text-slate-700">Contraseña</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            </span>
                            <input id="contrasena" type="password" name="contrasena" autocomplete="current-password" required
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100"
                                placeholder="Ingresa tu contraseña">
                        </div>
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2 font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-200">
                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                        Ingresar
                    </button>
                </form>

                <div class="mt-7 text-center">
                    <a href="/" class="inline-flex items-center rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i> Volver a la página de inicio
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
