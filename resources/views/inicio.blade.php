@extends('layouts.app')

@section('title', config('bufete.nombre') . ' | Asesoría jurídica profesional')

@section('content')

{{-- Portada --}}
<section class="relative isolate overflow-hidden bg-navy bg-cover bg-center text-white"
         style="background-image: url('{{ asset('img/portada.jpg') }}')">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-navy/95 via-navy/80 to-navy/45"></div>
    <div class="mx-auto max-w-7xl px-6 py-24 md:py-36 lg:px-8">
        <p class="mb-6 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-gold sm:text-sm">
            <span class="h-px w-8 bg-gold"></span>
            Asesoría jurídica integral
        </p>
        <h1 class="max-w-4xl font-serif text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl md:text-6xl lg:text-7xl">
            {{ config('bufete.eslogan') }}
        </h1>
        <p class="mt-7 max-w-2xl text-base leading-7 text-slate-200 sm:text-lg sm:leading-8">
            Acompañamos a personas y empresas en cada etapa de su proceso legal, con atención cercana y resultados claros.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="#contacto" class="inline-flex items-center justify-center rounded-full bg-gold px-7 py-3.5 font-semibold text-navy shadow-lg shadow-black/15 transition hover:-translate-y-0.5 hover:bg-white">
                Agendar consulta
                @svg('heroicon-o-arrow-up-right', 'ml-2 size-4')
            </a>
            <a href="#areas" class="inline-flex items-center justify-center rounded-full border border-white/40 bg-white/5 px-7 py-3.5 font-semibold text-white backdrop-blur-sm transition hover:border-white hover:bg-white/10">
                Ver áreas de práctica
            </a>
        </div>
    </div>
</section>

{{-- Áreas de práctica --}}
<section id="areas" class="scroll-mt-24 bg-slate-50 py-20 md:py-24">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-gold">Experiencia a su servicio</p>
        <h2 class="mt-3 text-center font-serif text-3xl font-semibold text-navy md:text-4xl">Áreas de práctica</h2>
        <p class="mt-3 text-center text-slate-500">Asesoría especializada para cada necesidad</p>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('bufete.areas') as $area)
                <div class="group rounded-2xl border border-slate-200/80 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-gold/60 hover:shadow-xl hover:shadow-navy/5 sm:p-8">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-navy text-gold transition duration-300 group-hover:bg-gold group-hover:text-navy">
                        @svg('heroicon-o-' . $area[0], 'size-7')
                    </div>
                    <h3 class="mt-6 font-serif text-xl font-semibold text-navy">{{ $area[1] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $area[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Cómo trabajamos --}}
<section id="proceso" class="scroll-mt-24 py-20 md:py-24">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-gold">Un proceso claro</p>
        <h2 class="mt-3 text-center font-serif text-3xl font-semibold text-navy md:text-4xl">Cómo trabajamos</h2>
        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('bufete.pasos') as $paso)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-sm">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gold/15 font-serif text-xl font-bold text-navy ring-1 ring-gold/30">{{ $paso[0] }}</div>
                    <h3 class="mt-5 font-semibold text-navy">{{ $paso[1] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $paso[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Nosotros --}}
<section id="nosotros" class="scroll-mt-24 bg-slate-50 py-20 md:py-24">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 md:grid-cols-2 lg:px-8">
        <div>
            @if (file_exists(public_path('img/nosotros.jpg')))
                <img src="{{ asset('img/nosotros.jpg') }}" alt="Nuestro bufete"
                     class="h-80 w-full rounded-3xl object-cover shadow-xl shadow-navy/10 sm:h-96">
            @else
                <div class="h-80 w-full rounded-3xl bg-navy sm:h-96"></div>
            @endif
        </div>

        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">Compromiso y confianza</p>
            <h2 class="mt-3 font-serif text-3xl font-semibold text-navy md:text-4xl">Sobre nosotros</h2>
            <p class="mt-6 leading-7 text-slate-600">
                Somos un bufete comprometido con la defensa de los intereses de nuestros clientes.
                Combinamos conocimiento jurídico, ética profesional y trato humano para ofrecer
                soluciones claras y oportunas.
            </p>
            <p class="mt-4 leading-7 text-slate-600">
                Cada caso es único, por eso le dedicamos el tiempo necesario para entender su
                situación y diseñar la mejor estrategia.
            </p>

            <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                @foreach (config('bufete.cifras') as $cifra)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="font-serif text-2xl font-bold text-navy">{{ $cifra[0] }}</p>
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ $cifra[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Equipo --}}
<section id="equipo" class="scroll-mt-24 py-20 md:py-24">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-gold">Atención personal</p>
        <h2 class="mt-3 text-center font-serif text-3xl font-semibold text-navy md:text-4xl">Nuestro equipo</h2>
        <div class="mt-12 grid gap-8 sm:grid-cols-3">
            @foreach (config('bufete.equipo') as $persona)
                @php
                    $foto = $persona[3] ?? null;
                    $existe = $foto && file_exists(public_path('img/equipo/' . $foto));
                @endphp
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-navy/5">
                    @if ($existe)
                        <img src="{{ asset('img/equipo/' . $foto) }}" alt="{{ $persona[1] }}"
                             class="mx-auto size-36 rounded-full border-4 border-gold/25 object-cover shadow-md sm:size-40">
                    @else
                        <div class="mx-auto flex size-36 items-center justify-center rounded-full bg-navy font-serif text-4xl font-bold text-gold sm:size-40">{{ $persona[0] }}</div>
                    @endif
                    <h3 class="mt-5 font-serif text-lg font-semibold text-navy">{{ $persona[1] }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $persona[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Preguntas frecuentes --}}
<section id="preguntas" class="scroll-mt-24 bg-slate-50 py-20 md:py-24">
    <div class="mx-auto max-w-3xl px-6">
        <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-gold">Estamos para orientarle</p>
        <h2 class="mt-3 text-center font-serif text-3xl font-semibold text-navy md:text-4xl">Preguntas frecuentes</h2>
        <div class="mt-10 grid gap-3">
            @foreach (config('bufete.preguntas') as $p)
                <details class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm open:border-gold/40">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium text-navy [&::-webkit-details-marker]:hidden">
                        {{ $p[0] }}
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-50 text-gold transition group-open:rotate-45">
                            @svg('heroicon-o-plus', 'size-4')
                        </span>
                    </summary>
                    <p class="mt-4 border-t border-slate-100 pt-4 text-sm leading-6 text-slate-600">{{ $p[1] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- Contacto --}}
<section id="contacto" class="scroll-mt-24 py-20 md:py-24">
    <div class="mx-auto max-w-7xl px-6 text-center lg:px-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">Conversemos</p>
        <h2 class="mt-3 font-serif text-3xl font-semibold text-navy md:text-4xl">Contáctenos</h2>
        <p class="mt-4 text-slate-600">Cuéntenos su caso y le responderemos a la brevedad.</p>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            @php
                $contacto = [
                    ['map-pin',  'Dirección', config('bufete.direccion')],
                    ['phone',    'Teléfono',  config('bufete.telefono')],
                    ['envelope', 'Correo',    config('bufete.correo')],
                    ['clock',    'Horario',   config('bufete.horario')],
                ];
            @endphp

            @foreach ($contacto as $dato)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-navy text-gold">
                        @svg('heroicon-o-' . $dato[0], 'size-6')
                    </div>
                    <p class="mt-3 text-xs font-semibold uppercase tracking-widest text-gold">{{ $dato[1] }}</p>
                    <p class="mt-1">{{ $dato[2] }}</p>
                </div>
            @endforeach
        </div>

        <a href="https://wa.me/{{ config('bufete.whatsapp') }}?text={{ urlencode('Hola, quisiera una consulta.') }}"
           target="_blank" rel="noopener"
           class="mt-10 inline-flex items-center justify-center rounded-full bg-navy px-8 py-3.5 font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-gold">
            Escribir por WhatsApp
        </a>
    </div>
</section>

@endsection