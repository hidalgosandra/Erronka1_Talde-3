@extends('layouts.app')

@section('title', 'Página no encontrada · Ikasgune')

@section('content')
    <section class="container dashboard-section empty-state" aria-labelledby="not-found-title">
        <p class="eyebrow">404 · IKASGUNE</p>
        <h1 id="not-found-title">Esta página se ha<br><em>perdido por el camino.</em></h1>
        <p class="hero-description">Puede que el enlace haya cambiado, pero siempre puedes volver a empezar.</p>
        <div class="hero-actions"><a class="button" href="{{ route('inicio') }}">Volver al inicio</a><a class="text-link" href="{{ route('courses.index') }}">Explorar cursos →</a></div>
    </section>
@endsection
