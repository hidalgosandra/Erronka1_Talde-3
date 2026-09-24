@extends('layouts.app')

@section('title', 'Mi espacio · Ikasgune')

@section('content')
    <section class="container dashboard-section" aria-labelledby="dashboard-title">
        <p class="eyebrow">MI ESPACIO · IKASGUNE</p>
        <h1 id="dashboard-title">Hola, {{ auth()->user()->name }}.</h1>
        <p class="hero-description">Continúa donde lo dejaste y convierte cada sesión en un nuevo avance.</p>
        <div class="admin-stats dashboard-stats"><article class="auth-card"><h2>En progreso</h2><p class="stat-value">{{ $enrollments->count() }}</p><span class="auth-description">cursos guardados</span></article><article class="auth-card"><h2>Completados</h2><p class="stat-value">0</p><span class="auth-description">sigue aprendiendo</span></article><article class="auth-card"><h2>Racha</h2><p class="stat-value">1 <small>día</small></p><span class="auth-description">vuelve mañana</span></article></div>
        <div class="auth-card account-card">
            <h2>Tu cuenta</h2>
            <dl><dt>Nombre</dt><dd>{{ auth()->user()->name }}</dd><dt>Correo electrónico</dt><dd>{{ auth()->user()->email }}</dd></dl>
            <div class="hero-actions"><a class="button" href="{{ route('courses.mine') }}">Mis cursos</a><a class="text-link" href="{{ route('courses.index') }}">Ver catálogo →</a></div>
        </div>
        <section class="learning-panel" aria-labelledby="learning-title"><div class="section-heading"><div><p class="eyebrow">TU RECORRIDO</p><h2 id="learning-title">Retoma tu aprendizaje.</h2></div><a class="text-link" href="{{ route('courses.index') }}">Descubrir más →</a></div><div class="feature-grid">@forelse($enrollments as $enrollment)<article class="feature-card course-card"><span class="preview-label">EN PROGRESO</span><h3>{{ $enrollment->course->title }}</h3><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"><span></span></div><p class="course-meta">0% completado · inscrito el {{ $enrollment->created_at->format('d/m/Y') }}</p><a class="button button-small" href="{{ route('courses.show', $enrollment->course) }}">Continuar →</a></article>@empty<p class="auth-description">Aún no tienes cursos. Empieza explorando el catálogo.</p>@endforelse</div></section>
    </section>
@endsection
