<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('bufete.nombre'))</title>
    <meta name="description" content="@yield('description', config('bufete.eslogan'))">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { navy: '#0f1f3d', gold: '#b8924a' },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="font-sans text-slate-700 antialiased">

    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="font-serif text-xl font-bold text-navy">{{ config('bufete.nombre') }}</a>
            <nav class="hidden md:flex gap-8 text-sm font-medium">
                <a href="#areas" class="hover:text-gold">Áreas</a>
                <a href="#proceso" class="hover:text-gold">Cómo trabajamos</a>
                <a href="#nosotros" class="hover:text-gold">Nosotros</a>
                <a href="#equipo" class="hover:text-gold">Equipo</a>
                <a href="#preguntas" class="hover:text-gold">Preguntas</a>
                <a href="#contacto" class="hover:text-gold">Contacto</a>
            </nav>
            <a href="#contacto" class="bg-navy text-white text-sm font-medium px-4 py-2 rounded hover:bg-gold transition">Consulta</a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-navy text-slate-300 py-10">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between gap-4 text-sm">
            <p>© {{ date('Y') }} {{ config('bufete.nombre') }}. Todos los derechos reservados.</p>
            <p>{{ config('bufete.ciudad') }}</p>
        </div>
    </footer>

    {{-- Botón flotante de WhatsApp --}}
    <a href="https://wa.me/{{ config('bufete.whatsapp') }}?text={{ urlencode('Hola, quisiera una consulta.') }}"
       target="_blank" rel="noopener"
       class="fixed bottom-5 right-5 z-50 bg-green-500 text-white font-medium text-sm px-5 py-3 rounded-full shadow-lg hover:bg-green-600">
        WhatsApp
    </a>

</body>
</html>