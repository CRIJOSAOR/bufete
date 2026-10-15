@extends('layouts.app')

@section('title', config('bufete.nombre') . ' | Asesoría jurídica profesional')

@section('content')

{{-- Portada --}}
<section class="bg-navy text-white">
    <div class="max-w-6xl mx-auto px-6 py-24 md:py-32">
        <p class="text-gold font-medium tracking-widest text-sm uppercase mb-4">Asesoría jurídica integral</p>
        <h1 class="font-serif text-4xl md:text-6xl font-bold leading-tight max-w-3xl">
            {{ config('bufete.eslogan') }}
        </h1>
        <p class="mt-6 text-lg text-slate-300 max-w-2xl">
            Acompañamos a personas y empresas en cada etapa de su proceso legal, con atención cercana y resultados claros.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="#contacto" class="bg-gold text-white font-medium px-6 py-3 rounded hover:opacity-90">Agendar consulta</a>
            <a href="#areas" class="border border-white/40 px-6 py-3 rounded hover:bg-white/10">Ver áreas de práctica</a>
        </div>
    </div>
</section>

{{-- Áreas de práctica --}}
<section id="areas" class="py-20 bg-slate-50">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-navy text-center">Áreas de práctica</h2>
        <p class="text-center mt-3 text-slate-500">Asesoría especializada para cada necesidad</p>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('bufete.areas') as $area)
                <div class="bg-white p-6 rounded-lg border border-slate-200 hover:shadow-lg hover:border-gold transition">
                    <div class="text-3xl">{{ $area[0] }}</div>
                    <h3 class="mt-4 font-serif text-xl font-semibold text-navy">{{ $area[1] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $area[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Cómo trabajamos --}}
<section id="proceso" class="py-20">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-navy text-center">Cómo trabajamos</h2>
        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('bufete.pasos') as $paso)
                <div class="text-center">
                    <div class="mx-auto w-12 h-12 rounded-full bg-gold text-white font-serif text-xl font-bold flex items-center justify-center">{{ $paso[0] }}</div>
                    <h3 class="mt-4 font-semibold text-navy">{{ $paso[1] }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $paso[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Nosotros --}}
<section id="nosotros" class="py-20 bg-slate-50">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="font-serif text-3xl md:text-4xl font-bold text-navy">Sobre nosotros</h2>
            <p class="mt-6 leading-relaxed">
                Somos un bufete comprometido con la defensa de los intereses de nuestros clientes.
                Combinamos conocimiento jurídico, ética profesional y trato humano para ofrecer
                soluciones claras y oportunas.
            </p>
            <p class="mt-4 leading-relaxed">
                Cada caso es único, por eso le dedicamos el tiempo necesario para entender su
                situación y diseñar la mejor estrategia.
            </p>
        </div>
        <div class="grid grid-cols-3 gap-4 text-center">
            @foreach (config('bufete.cifras') as $cifra)
                <div class="bg-navy text-white rounded-lg p-6">
                    <p class="font-serif text-3xl font-bold text-gold">{{ $cifra[0] }}</p>
                    <p class="text-xs mt-1">{{ $cifra[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Equipo --}}
<section id="equipo" class="py-20">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-navy text-center">Nuestro equipo</h2>
        <div class="mt-12 grid gap-8 sm:grid-cols-3">
            @foreach (config('bufete.equipo') as $persona)
                <div class="text-center">
                    <div class="mx-auto w-28 h-28 rounded-full bg-navy text-gold font-serif text-3xl font-bold flex items-center justify-center">{{ $persona[0] }}</div>
                    <h3 class="mt-4 font-serif text-lg font-semibold text-navy">{{ $persona[1] }}</h3>
                    <p class="text-sm text-slate-500">{{ $persona[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Preguntas frecuentes --}}
<section id="preguntas" class="py-20 bg-slate-50">
    <div class="max-w-3xl mx-auto px-6">
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-navy text-center">Preguntas frecuentes</h2>
        <div class="mt-10 space-y-3">
            @foreach (config('bufete.preguntas') as $p)
                <details class="bg-white border border-slate-200 rounded-lg p-5 group">
                    <summary class="font-medium text-navy cursor-pointer">{{ $p[0] }}</summary>
                    <p class="mt-3 text-sm text-slate-600">{{ $p[1] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- Contacto --}}
<section id="contacto" class="py-20">
    <div class="max-w-6xl mx-auto px-6 text-center">
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-navy">Contáctenos</h2>
        <p class="mt-4 text-slate-600">Cuéntenos su caso y le responderemos a la brevedad.</p>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div class="p-6 border border-slate-200 rounded-lg">
                <p class="text-2xl">📍</p>
                <p class="mt-2 font-semibold text-navy">Dirección</p>
                <p class="mt-1">{{ config('bufete.direccion') }}</p>
            </div>
            <div class="p-6 border border-slate-200 rounded-lg">
                <p class="text-2xl">📞</p>
                <p class="mt-2 font-semibold text-navy">Teléfono</p>
                <p class="mt-1">{{ config('bufete.telefono') }}</p>
            </div>
            <div class="p-6 border border-slate-200 rounded-lg">
                <p class="text-2xl">✉️</p>
                <p class="mt-2 font-semibold text-navy">Correo</p>
                <p class="mt-1">{{ config('bufete.correo') }}</p>
            </div>
            <div class="p-6 border border-slate-200 rounded-lg">
                <p class="text-2xl">🕘</p>
                <p class="mt-2 font-semibold text-navy">Horario</p>
                <p class="mt-1">{{ config('bufete.horario') }}</p>
            </div>
        </div>

        <a href="https://wa.me/{{ config('bufete.whatsapp') }}?text={{ urlencode('Hola, quisiera una consulta.') }}"
           target="_blank" rel="noopener"
           class="inline-block mt-10 bg-navy text-white font-medium px-8 py-3 rounded hover:bg-gold transition">
            Escribir por WhatsApp
        </a>
    </div>
</section>

@endsection