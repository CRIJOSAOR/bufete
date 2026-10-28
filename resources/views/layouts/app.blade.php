<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('bufete.nombre'))</title>
    <meta name="description" content="@yield('description', config('bufete.eslogan'))">
    <meta name="theme-color" content="#173c36">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-700 antialiased selection:bg-gold/20 selection:text-navy">

    <header class="sticky top-0 z-50 border-b border-white/10 bg-navy shadow-lg shadow-navy/10">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-5 sm:px-6 lg:px-8">
            <a href="/" class="flex shrink-0 items-center gap-3" aria-label="{{ config('bufete.nombre') }} - inicio">
                <span class="flex h-14 items-center rounded-xl bg-white px-2 shadow-sm">
                    <img src="{{ asset('img/logo.png') }}" alt=""
                         class="h-12 w-auto object-contain">
                </span>
                <span class="grid gap-0.5">
                    <span class="font-serif text-base font-bold leading-tight tracking-wide text-white sm:text-lg">{{ config('bufete.nombre') }}</span>
                    <span class="hidden text-[10px] font-medium uppercase tracking-[0.14em] text-gold sm:block">Abogados &amp; Consultores</span>
                </span>
            </a>

            <nav aria-label="Navegación principal" class="hidden items-center gap-1 xl:flex">
                <a href="#areas" class="rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-gold">Áreas</a>
                <a href="#proceso" class="rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-gold">Cómo trabajamos</a>
                <a href="#nosotros" class="rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-gold">Nosotros</a>
                <a href="#equipo" class="rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-gold">Equipo</a>
                <a href="#preguntas" class="rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-gold">Preguntas</a>
                <a href="#contacto" class="rounded-full px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-gold">Contacto</a>
            </nav>

            <a href="#contacto" class="hidden items-center rounded-full bg-gold px-5 py-3 text-sm font-semibold text-navy shadow-sm transition hover:bg-white hover:shadow-md xl:inline-flex">
                Agendar consulta
            </a>

            <details class="relative xl:hidden">
                <summary aria-label="Abrir menú de navegación"
                         class="flex size-11 cursor-pointer list-none items-center justify-center rounded-full border border-white/20 text-white transition hover:bg-white/10 [&::-webkit-details-marker]:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
                    </svg>
                </summary>
                <nav aria-label="Navegación móvil" class="absolute right-0 top-full mt-3 grid w-64 gap-1 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                    <a href="#areas" class="rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-navy">Áreas de práctica</a>
                    <a href="#proceso" class="rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-navy">Cómo trabajamos</a>
                    <a href="#nosotros" class="rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-navy">Nosotros</a>
                    <a href="#equipo" class="rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-navy">Equipo</a>
                    <a href="#preguntas" class="rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-navy">Preguntas frecuentes</a>
                    <a href="#contacto" class="rounded-xl bg-navy px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-gold">Agendar consulta</a>
                </nav>
            </details>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-navy py-10 text-slate-300">
        <div class="mx-auto flex max-w-7xl flex-col justify-between gap-4 px-6 text-sm md:flex-row">
            <p>© {{ date('Y') }} {{ config('bufete.nombre') }}. Todos los derechos reservados.</p>
            <p>{{ config('bufete.ciudad') }}</p>
        </div>
    </footer>

    <a href="https://wa.me/{{ config('bufete.whatsapp') }}?text={{ urlencode('Hola, quisiera una consulta.') }}"
       target="_blank" rel="noopener"
       aria-label="Escríbanos por WhatsApp"
       class="fixed bottom-5 right-5 z-50 inline-flex items-center gap-2 rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-green-950/20 transition hover:-translate-y-0.5 hover:bg-green-700">
        @svg('heroicon-o-chat-bubble-left-right', 'size-5')
        <span>WhatsApp</span>
    </a>

</body>
</html>