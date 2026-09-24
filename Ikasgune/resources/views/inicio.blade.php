@extends('layouts.app')

@section('content')
    <section class="hero container" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="status-dot" aria-hidden="true"></span> ONGI ETORRI · BIENVENIDO/A</p>
            <h1 id="hero-title">Tu próximo paso<br>empieza <em>aquí.</em></h1>
            <p class="hero-description">Un espacio para aprender, compartir ideas y construir lo que viene. Bienvenido a Ikasgune.</p>
            <div class="hero-actions"><a class="button" href="{{ route('courses.index') }}">Explorar cursos <span aria-hidden="true">↗</span></a><a class="text-link" href="#espacio">Conócenos <span aria-hidden="true">→</span></a></div>
            <p class="hero-note">Curiosidad para empezar. Un lugar para crecer.</p>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="art-grid"></div><span class="art-label">IDEAS QUE CRECEN</span>
            <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
            <div class="learning-card"><span>01 / IKASGUNE</span><strong>Aprende.<br>Comparte.<br><em>Crece.</em></strong><span class="card-arrow">↗</span></div>
            <div class="floating-note"><span>✳</span> Cada idea es<br>un nuevo comienzo.</div>
            <span class="art-bottom">EL FUTURO SE CONSTRUYE APRENDIENDO</span>
        </div>
    </section>
    <section id="espacio" class="space-section" aria-labelledby="space-title">
        <div class="container">
            <div class="section-heading"><div><p class="eyebrow">UN LUGAR, MUCHAS POSIBILIDADES</p><h2 id="space-title">Aprender nos conecta.</h2></div><p>Las buenas ideas empiezan con una pregunta.<br>Y crecen cuando las compartimos.</p></div>
            <div class="feature-grid">
                <article class="feature-card"><span class="feature-icon" aria-hidden="true">↗</span><span class="feature-number">01</span><h3>Explora</h3><p>Abre la puerta a nuevas ideas y encuentra aquello que despierta tu curiosidad.</p></article>
                <article class="feature-card"><span class="feature-icon" aria-hidden="true">✳</span><span class="feature-number">02</span><h3>Comparte</h3><p>Aprender también es escuchar, colaborar y descubrir otras formas de ver las cosas.</p></article>
                <article class="feature-card"><span class="feature-icon" aria-hidden="true">◎</span><span class="feature-number">03</span><h3>Crece</h3><p>Convierte cada pequeño avance en un paso más hacia lo que quieres conseguir.</p></article>
            </div>
        </div>
    </section>
    <section id="primeros-pasos" class="container getting-started" aria-labelledby="steps-title">
        <div><p class="eyebrow">ESTO ES SOLO EL PRINCIPIO</p><h2 id="steps-title">Un nuevo espacio.<br>Mucho por descubrir.</h2></div>
        <div class="coming-soon"><span class="preview-label">CURSOS IKASGUNE</span><p>Descubre el catálogo y elige qué quieres aprender. Inicia sesión o crea una cuenta para inscribirte y consultar tus cursos.</p><a class="text-link" href="{{ route('courses.index') }}">Ver cursos <span aria-hidden="true">→</span></a></div>
    </section>
@endsection
