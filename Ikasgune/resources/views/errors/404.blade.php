@extends('layouts.app')

@section('title', __('Página no encontrada · Eskolak'))

@section('content')
    <section class="container dashboard-section empty-state" aria-labelledby="not-found-title">
        <p class="eyebrow">{{ __('404 · ESKOLAK') }}</p>
        <h1 id="not-found-title">{{ __('Esta página se ha') }}<br><em>{{ __('perdido por el camino.') }}</em></h1>
        <p class="hero-description">{{ __('Puede que el enlace haya cambiado, pero siempre puedes volver a empezar.') }}</p>
        <div class="hero-actions"><a class="button" href="{{ route('inicio') }}">{{ __('Volver al inicio') }}</a><a class="text-link" href="{{ route('courses.index') }}">{{ __('Explorar cursos →') }}</a></div>
    </section>
@endsection
