<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14532d">
    <meta name="description" content="CAPPIAS Camiri: organización que impulsa el desarrollo de la pequeña industria, artesanías y servicios.">
    <title>CAPPIAS Camiri | Artesanía, industria y servicios</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Manrope', sans-serif; }
        .hero-overlay { background: linear-gradient(90deg, rgba(7, 37, 25, .88) 0%, rgba(7, 37, 25, .65) 55%, rgba(7, 37, 25, .25) 100%); }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-white/95 shadow-sm backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:h-20 lg:px-8" aria-label="Navegación principal">
            <a href="/" class="flex min-w-0 items-center gap-3" aria-label="CAPPIAS Camiri, inicio">
                <img src="{{ asset('img/logo_cappias.png') }}" class="h-12 w-auto object-contain sm:h-14" alt="CAPPIAS Camiri">
            </a>
            <div class="flex items-center gap-2 sm:gap-4">
                <button type="button" data-theme-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-700 transition hover:bg-slate-50" aria-label="Activar modo oscuro" aria-pressed="false" title="Activar modo oscuro">
                    <i class="fas fa-moon" aria-hidden="true"></i>
                </button>
                <a href="/vitrina/cursos" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-green-50 hover:text-green-800 sm:inline-flex">Ver Cursos</a>
                <a href="/vitrina/productos" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-green-50 hover:text-green-800 sm:inline-flex">Ver Productos</a>
                <a href="/login" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-green-800 sm:px-4 sm:text-sm">
                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                    <span>Acceso Personal</span>
                </a>
            </div>
        </nav>
        <div class="flex justify-center gap-6 border-t border-slate-100 px-4 py-2 sm:hidden">
            <a href="/vitrina/cursos" class="text-xs font-bold text-slate-700 hover:text-green-800">Ver Cursos</a>
            <a href="/vitrina/productos" class="text-xs font-bold text-slate-700 hover:text-green-800">Ver Productos</a>
        </div>
    </header>

    @if(session('success'))
        <div class="fixed left-1/2 top-24 z-[60] w-[calc(100%-2rem)] max-w-3xl -translate-x-1/2 rounded-xl border border-green-300 bg-green-50 px-5 py-4 text-green-900 shadow-xl" role="status">
            <i class="fa-solid fa-circle-check mr-2 text-green-700" aria-hidden="true"></i>{{ session('success') }}
        </div>
    @endif

    <main>
        <section class="relative flex min-h-[680px] items-center overflow-hidden bg-green-950 pt-28 sm:min-h-[740px] sm:pt-20">
            <img src="{{ asset('img/banner_cappias.jpg') }}" alt="Artesanía y trabajo productivo de CAPPIAS Camiri" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="hero-overlay absolute inset-0"></div>
            <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-green-950/50 to-transparent"></div>
            <div class="relative mx-auto w-full max-w-7xl px-5 py-20 sm:px-8 lg:px-10">
                <div class="max-w-3xl">
                    <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[.15em] text-green-50 backdrop-blur sm:text-sm">
                        <i class="fa-solid fa-location-dot text-green-300" aria-hidden="true"></i>
                        Camiri · Santa Cruz · Bolivia
                    </span>
                    <h1 class="font-display text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-6xl lg:text-7xl">CAPPIAS <span class="text-green-300">CAMIRI</span></h1>
                    <p class="mt-6 max-w-2xl text-base leading-7 text-green-50 sm:text-xl sm:leading-8">
                        Cámara Provincial de la Pequeña Industria, Artesanías y Servicios
                    </p>
                    <p class="mt-4 max-w-xl text-sm leading-6 text-white/80 sm:text-base">
                        Una comunidad que une esfuerzos para fortalecer la producción, el talento y el desarrollo de nuestros asociados.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="/vitrina/cursos" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-blue-500 sm:text-base">
                            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Ver Cursos
                        </a>
                        <a href="/vitrina/productos" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-green-500 sm:text-base">
                            <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i> Ver Productos
                        </a>
                    </div>
                </div>
            </div>
            <a href="#institucion" class="absolute bottom-6 left-1/2 hidden -translate-x-1/2 items-center gap-2 text-xs font-semibold text-white/80 transition hover:text-white sm:flex">
                Conoce nuestra institución <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
            </a>
        </section>

        <section id="institucion" class="scroll-mt-24 bg-white py-16 sm:py-24">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 sm:px-8 lg:grid-cols-[1.1fr_.9fr] lg:items-center lg:px-10">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.18em] text-green-700">Nuestra institución</p>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Impulsamos el talento que hace crecer Camiri</h2>
                    <p class="mt-5 text-base leading-8 text-slate-600">
                        CAPPIAS Camiri es una organización comprometida con el desarrollo de la pequeña industria, las artesanías y los servicios. Desde su fundación, trabaja junto a sus asociados para fortalecer sus capacidades, su producción y sus oportunidades.
                    </p>
                    <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <i class="fa-regular fa-calendar-check text-lg text-green-700" aria-hidden="true"></i>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Fundación</p>
                            <p class="mt-1 font-bold text-slate-900">21 de septiembre de 2004</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <i class="fa-solid fa-scale-balanced text-lg text-blue-700" aria-hidden="true"></i>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Personería Jurídica</p>
                            <p class="mt-1 font-bold text-slate-900">106/06</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <i class="fa-solid fa-people-group text-lg text-green-700" aria-hidden="true"></i>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Afiliación</p>
                            <p class="mt-1 font-bold text-slate-900">CADEPIA · Santa Cruz</p>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-3 rounded-3xl bg-green-100"></div>
                    <img src="{{ asset('img/banner_cappias.jpg') }}" alt="Comunidad productiva y artesanal de CAPPIAS Camiri" class="relative h-72 w-full rounded-2xl object-cover shadow-xl sm:h-[420px]">
                    <div class="absolute -bottom-5 left-5 right-5 rounded-xl bg-white p-4 shadow-lg sm:left-8 sm:right-8 sm:p-5">
                        <p class="flex items-center gap-3 text-sm font-bold text-slate-800 sm:text-base">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-800"><i class="fa-solid fa-handshake-angle" aria-hidden="true"></i></span>
                            Unidos por el desarrollo de nuestros asociados
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="mx-auto mb-10 max-w-2xl text-center sm:mb-14">
                    <p class="text-xs font-extrabold uppercase tracking-[.18em] text-blue-700">Nuestro propósito</p>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Misión y visión</h2>
                    <p class="mt-4 text-slate-600">El compromiso que guía nuestro trabajo y el horizonte que construimos juntos.</p>
                </div>
                <div class="grid gap-6 lg:grid-cols-2">
                    <article class="rounded-2xl border border-green-100 bg-white p-6 shadow-sm sm:p-9">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-xl text-green-800"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
                            <h3 class="font-display text-2xl font-extrabold text-slate-900">Misión</h3>
                        </div>
                        <p class="mt-6 text-base leading-8 text-slate-600">
                            Trabaja fundamentalmente para apoyar a sus asociados dentro de un marco de desarrollo sostenido y sostenible, para obtener recursos económicos fortaleciendo la producción artesanal industrial y de servicios, busca mejorar íntegramente la calidad de vida de sus asociados, elevando la capacidad productiva, competitiva y asociativa.
                        </p>
                    </article>
                    <article class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm sm:p-9">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-xl text-blue-800"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
                            <h3 class="font-display text-2xl font-extrabold text-slate-900">Visión</h3>
                        </div>
                        <p class="mt-6 text-base leading-8 text-slate-600">
                            Es una organización que promueve el desarrollo artesanal, industrial y de servicios; con solidaridad, equidad, eficiencia, competitividad y sostenibilidad, propiciando el empleo de instrumentos de gestión y tecnología con una visión de futuro.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="bg-white py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="mb-10 flex flex-col gap-4 sm:mb-14 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-extrabold uppercase tracking-[.18em] text-green-700">Hacia dónde avanzamos</p>
                        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Nuestros objetivos</h2>
                        <p class="mt-4 text-slate-600">Seis líneas de acción para fortalecer al sector y a quienes lo integran.</p>
                    </div>
                    <span class="hidden rounded-full bg-green-50 px-4 py-2 text-sm font-bold text-green-800 sm:inline-flex">Desarrollo · Asociatividad · Oportunidades</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article class="group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-green-200 hover:shadow-lg">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-lg text-green-800 group-hover:bg-green-700 group-hover:text-white"><i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i></span>
                        <h3 class="mt-5 font-display text-lg font-bold text-slate-900">Atender las necesidades del sector</h3>
                        <p class="mt-2 leading-7 text-slate-600">Conocer y resolver los problemas que afectan a la pequeña industria, las artesanías y los servicios.</p>
                    </article>
                    <article class="group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-800 group-hover:bg-blue-700 group-hover:text-white"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span>
                        <h3 class="mt-5 font-display text-lg font-bold text-slate-900">Crear espacios colectivos</h3>
                        <p class="mt-2 leading-7 text-slate-600">Organizar eventos que conecten a los asociados con la comunidad y nuevas oportunidades.</p>
                    </article>
                    <article class="group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-green-200 hover:shadow-lg">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-lg text-green-800 group-hover:bg-green-700 group-hover:text-white"><i class="fa-solid fa-seedling" aria-hidden="true"></i></span>
                        <h3 class="mt-5 font-display text-lg font-bold text-slate-900">Promover el desarrollo sostenible</h3>
                        <p class="mt-2 leading-7 text-slate-600">Impulsar políticas e iniciativas responsables para el crecimiento del sector.</p>
                    </article>
                    <article class="group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-800 group-hover:bg-blue-700 group-hover:text-white"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></span>
                        <h3 class="mt-5 font-display text-lg font-bold text-slate-900">Fortalecer capacidades</h3>
                        <p class="mt-2 leading-7 text-slate-600">Propiciar la capacitación y el desarrollo de las personas y actividades del sector.</p>
                    </article>
                    <article class="group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-green-200 hover:shadow-lg">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-lg text-green-800 group-hover:bg-green-700 group-hover:text-white"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span>
                        <h3 class="mt-5 font-display text-lg font-bold text-slate-900">Ampliar nuestra comunidad</h3>
                        <p class="mt-2 leading-7 text-slate-600">Promover la incorporación de nuevos asociados y fortalecer la acción conjunta.</p>
                    </article>
                    <article class="group rounded-2xl border border-slate-200 p-6 transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-800 group-hover:bg-blue-700 group-hover:text-white"><i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i></span>
                        <h3 class="mt-5 font-display text-lg font-bold text-slate-900">Gestionar recursos y proyectos</h3>
                        <p class="mt-2 leading-7 text-slate-600">Buscar recursos financieros que hagan posibles proyectos para el desarrollo del sector.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[.18em] text-green-700">Creaciones locales</p>
                        <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Productos destacados</h2>
                        <p class="mt-4 text-slate-600">Conoce algunos productos elaborados por nuestros asociados.</p>
                    </div>
                    <a href="/vitrina/productos" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                        <i class="fa-solid fa-store" aria-hidden="true"></i> Ver catálogo
                    </a>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse($productos as $producto)
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            @if($producto->fotografia_url)
                                <img src="{{ asset('storage/' . $producto->fotografia_url) }}" alt="Fotografía de {{ $producto->nombre }}" class="h-48 w-full object-cover">
                            @else
                                <div class="flex h-48 items-center justify-center bg-slate-100 text-slate-400">
                                    <i class="fa-solid fa-box-open text-4xl" aria-hidden="true"></i>
                                </div>
                            @endif
                            <div class="p-5">
                                <h3 class="font-display text-lg font-bold text-slate-900">{{ $producto->nombre }}</h3>
                                <p class="mt-2 text-xl font-extrabold text-green-700">Bs. {{ number_format($producto->precio, 2) }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="text-slate-600">Pronto publicaremos productos de nuestros asociados.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="bg-green-900 py-14 sm:py-18">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-5 text-center sm:px-8 md:flex-row md:text-left lg:px-10">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[.16em] text-green-300">Conoce lo que hacemos</p>
                    <h2 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">Talento local, oportunidades para todos</h2>
                </div>
                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <a href="/vitrina/cursos" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-bold text-green-900 transition hover:bg-green-50"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Ver Cursos</a>
                    <a href="/vitrina/productos" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-500"><i class="fa-solid fa-basket-shopping" aria-hidden="true"></i> Ver Productos</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-slate-950 text-slate-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 md:grid-cols-2 lg:px-10">
            <div>
                <a href="/" class="inline-flex items-center gap-3 text-white">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-700"><i class="fa-solid fa-handshake-angle" aria-hidden="true"></i></span>
                    <span class="font-display text-lg font-extrabold">CAPPIAS Camiri</span>
                </a>
                <p class="mt-4 max-w-lg text-sm leading-7 text-slate-400">Cámara Provincial de la Pequeña Industria, Artesanías y Servicios. Trabajando por el desarrollo de nuestros asociados desde 2004.</p>
            </div>
            <div class="md:justify-self-end">
                <h2 class="font-display text-sm font-bold uppercase tracking-wider text-white">Contacto institucional</h2>
                <p class="mt-4 flex items-start gap-3 text-sm text-slate-400">
                    <i class="fa-solid fa-location-dot mt-1 text-green-400" aria-hidden="true"></i>
                    <span>Camiri, Santa Cruz, Bolivia</span>
                </p>
                <p class="mt-3 flex items-start gap-3 text-sm text-slate-400">
                    <i class="fa-solid fa-building-columns mt-1 text-green-400" aria-hidden="true"></i>
                    <span>Afiliada a CADEPIA Santa Cruz</span>
                </p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-5 text-center text-xs text-slate-500 sm:px-8 md:flex-row md:justify-between md:text-left lg:px-10">
                <p>&copy; {{ date('Y') }} CAPPIAS Camiri. Todos los derechos reservados.</p>
                <p>Fundada el 21 de septiembre de 2004 · Personería Jurídica 106/06</p>
            </div>
        </div>
    </footer>
</body>
</html>
