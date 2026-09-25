@extends('layouts.app')

@section('title', __('Mi espacio · Eskolak'))

@section('content')
    <section class="container dashboard-section" aria-labelledby="dashboard-title">
        <p class="eyebrow">{{ __('MI ESPACIO · ESKOLAK') }}</p>
        <h1 id="dashboard-title">{{ __('Hola,') }} {{ auth()->user()->name }}.</h1>
        <p class="hero-description">{{ __('Continúa donde lo dejaste y convierte cada sesión en un nuevo avance.') }}</p>
        <div class="admin-stats dashboard-stats"><article class="auth-card"><h2>{{ __('En progreso') }}</h2><p class="stat-value">{{ $enrollments->count() }}</p><span class="auth-description">{{ __('cursos guardados') }}</span></article><article class="auth-card"><h2>{{ __('Completados') }}</h2><p class="stat-value">0</p><span class="auth-description">{{ __('sigue aprendiendo') }}</span></article><article class="auth-card"><h2>{{ __('Racha') }}</h2><p class="stat-value">1 <small>{{ __('día') }}</small></p><span class="auth-description">{{ __('vuelve mañana') }}</span></article></div>
        <div class="auth-card account-card">
            <div class="account-details">
                <div class="account-heading"><span class="account-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><div><p class="eyebrow">{{ __('PERFIL ESKOLAK') }}</p><h2>{{ __('Tu cuenta') }}</h2></div></div>
                <dl><div><dt>{{ __('Nombre') }}</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>{{ __('Correo electrónico') }}</dt><dd>{{ auth()->user()->email }}</dd></div></dl>
            </div>
            <div class="account-actions"><a class="button" href="{{ route('courses.mine') }}">{{ __('Mis cursos') }}</a><a class="text-link" href="{{ route('courses.index') }}">{{ __('Ver catálogo →') }}</a></div>
        </div>
        <section class="learning-panel" aria-labelledby="learning-title"><div class="section-heading"><div><p class="eyebrow">{{ __('TU RECORRIDO') }}</p><h2 id="learning-title">{{ __('Retoma tu aprendizaje.') }}</h2></div><a class="text-link" href="{{ route('courses.index') }}">{{ __('Descubrir más →') }}</a></div><div class="feature-grid">@forelse($enrollments as $enrollment)<article class="feature-card course-card"><span class="preview-label">{{ __('EN PROGRESO') }}</span><h3>{{ $enrollment->course->localized('title') }}</h3><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"><span></span></div><p class="course-meta">{{ __('0% completado · inscrito el') }} {{ $enrollment->created_at->format('d/m/Y') }}</p><a class="button button-small" href="{{ route('courses.show', $enrollment->course) }}">{{ __('Continuar →') }}</a></article>@empty<p class="auth-description">{{ __('Aún no tienes cursos. Empieza explorando el catálogo.') }}</p>@endforelse</div></section>
    </section>
@endsection
