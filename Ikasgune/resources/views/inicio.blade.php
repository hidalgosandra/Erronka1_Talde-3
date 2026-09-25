@extends('layouts.app')

@section('content')
    <section class="hero container" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="status-dot" aria-hidden="true"></span> {{ __('ONGI ETORRI · BIENVENIDO/A') }}</p>
            <h1 id="hero-title">{{ __('Tu próximo paso') }}<br>{{ __('empieza') }} <em>{{ __('aquí.') }}</em></h1>
            <p class="hero-description">{{ __('Un espacio para aprender, compartir ideas y construir lo que viene. Bienvenido a Eskolak.') }}</p>
            <div class="hero-actions"><a class="button" href="{{ route('courses.index') }}">{{ __('Explorar cursos') }} <span aria-hidden="true">↗</span></a><a class="text-link" href="#espacio">{{ __('Conócenos') }} <span aria-hidden="true">→</span></a></div>
            <p class="hero-note">{{ __('Curiosidad para empezar. Un lugar para crecer.') }}</p>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="art-grid"></div><span class="art-label">{{ __('IDEAS QUE CRECEN') }}</span>
            <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
            <div class="learning-card"><span>01 / ESKOLAK</span><strong>{{ __('Aprende.') }}<br>{{ __('Comparte.') }}<br><em>{{ __('Crece.') }}</em></strong><span class="card-arrow">↗</span></div>
            <div class="floating-note"><span>✳</span> {{ __('Cada idea es') }}<br>{{ __('un nuevo comienzo.') }}</div>
            <span class="art-bottom">{{ __('EL FUTURO SE CONSTRUYE APRENDIENDO') }}</span>
        </div>
    </section>
    <section id="espacio" class="space-section" aria-labelledby="space-title">
        <div class="container">
            <div class="section-heading"><div><p class="eyebrow">{{ __('UN LUGAR, MUCHAS POSIBILIDADES') }}</p><h2 id="space-title">{{ __('Aprender nos conecta.') }}</h2></div><p>{{ __('Las buenas ideas empiezan con una pregunta.') }}<br>{{ __('Y crecen cuando las compartimos.') }}</p></div>
            <div class="home-showcase" aria-label="{{ __('Formas de aprender en Eskolak') }}">
                <article class="home-showcase-card showcase-dark">
                    <div class="showcase-card-copy"><div class="home-feature-top"><span class="showcase-kicker">{{ __('NUEVO CAMINO') }}</span><span class="feature-number">01</span></div><h3>{{ __('Explora') }}</h3><p>{{ __('Abre la puerta a nuevas ideas y encuentra aquello que despierta tu curiosidad.') }}</p></div>
                    <div class="showcase-art showcase-art-explore" aria-hidden="true"><span>↗</span></div>
                    <span class="home-feature-arrow" aria-hidden="true">↗</span>
                </article>
                <article class="home-showcase-card showcase-light">
                    <div class="showcase-card-copy"><div class="home-feature-top"><span class="showcase-kicker">{{ __('EN COMPAÑÍA') }}</span><span class="feature-number">02</span></div><h3>{{ __('Comparte') }}</h3><p>{{ __('Aprender también es escuchar, colaborar y descubrir otras formas de ver las cosas.') }}</p></div>
                    <div class="showcase-art showcase-art-share" aria-hidden="true"><span>✳</span></div>
                    <span class="home-feature-arrow" aria-hidden="true">↗</span>
                </article>
                <article class="home-showcase-card showcase-dark showcase-growth">
                    <div class="showcase-card-copy"><div class="home-feature-top"><span class="showcase-kicker">{{ __('PASO A PASO') }}</span><span class="feature-number">03</span></div><h3>{{ __('Crece') }}</h3><p>{{ __('Convierte cada pequeño avance en un paso más hacia lo que quieres conseguir.') }}</p></div>
                    <div class="showcase-art showcase-art-grow" aria-hidden="true"><span>◎</span></div>
                    <span class="home-feature-arrow" aria-hidden="true">↗</span>
                </article>
            </div>
        </div>
    </section>
    <section class="container proof-section" aria-labelledby="proof-title">
        <div class="section-heading"><div><p class="eyebrow">{{ __('ESKOLAK EN MOVIMIENTO') }}</p><h2 id="proof-title">{{ __('Pequeños pasos,') }}<br><em>{{ __('grandes cambios.') }}</em></h2></div><p>{{ __('Todo lo que necesitas para convertir la curiosidad en aprendizaje constante.') }}</p></div>
        <div class="proof-grid">
            <article><strong>{{ $courseCount }}+</strong><span>{{ __('cursos para descubrir') }}</span></article>
            <article><strong>{{ $learnerCount }}+</strong><span>{{ __('personas aprendiendo') }}</span></article>
            <article><strong>{{ $enrollmentCount }}+</strong><span>{{ __('inscripciones realizadas') }}</span></article>
        </div>
    </section>
    @if($featuredCourses->isNotEmpty())
        <section class="container featured-section" aria-labelledby="featured-title">
            <div class="section-heading"><div><p class="eyebrow">{{ __('SELECCIÓN ESKOLAK') }}</p><h2 id="featured-title">{{ __('Empieza por aquí.') }}</h2></div><a class="text-link" href="{{ route('courses.index') }}">{{ __('Ver todos los cursos →') }}</a></div>
            <div class="feature-grid">
                @foreach($featuredCourses as $course)
                    <article class="feature-card course-card"><span class="preview-label">{{ __($course->category) }} · {{ __($course->level) }}</span><h3>{{ $course->localized('title') }}</h3><p>{{ Str::limit($course->localized('description'), 120) }}</p><span class="course-meta">{{ $course->duration_minutes }} {{ __('min de aprendizaje') }}</span><a class="button button-small" href="{{ route('courses.show', $course) }}">{{ __('Ver curso →') }}</a></article>
                @endforeach
            </div>
        </section>
    @endif
    <section id="primeros-pasos" class="container getting-started" aria-labelledby="steps-title">
        <div><p class="eyebrow">{{ __('ESTO ES SOLO EL PRINCIPIO') }}</p><h2 id="steps-title">{{ __('Un nuevo espacio.') }}<br>{{ __('Mucho por descubrir.') }}</h2></div>
        <div class="coming-soon"><span class="preview-label">{{ __('CURSOS ESKOLAK') }}</span><p>{{ __('Descubre el catálogo y elige qué quieres aprender. Inicia sesión o crea una cuenta para inscribirte y consultar tus cursos.') }}</p><a class="text-link" href="{{ route('courses.index') }}">{{ __('Ver cursos') }} <span aria-hidden="true">→</span></a></div>
    </section>
    <section class="container final-cta" aria-labelledby="cta-title"><p class="eyebrow">{{ __('TU SIGUIENTE PASO') }}</p><h2 id="cta-title">{{ __('Tu próxima idea') }}<br><em>{{ __('empieza aquí.') }}</em></h2><a class="button" href="{{ route('courses.index') }}">{{ __('Explorar el catálogo') }} <span aria-hidden="true">↗</span></a></section>
@endsection
