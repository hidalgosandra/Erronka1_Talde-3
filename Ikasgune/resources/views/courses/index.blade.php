@extends('layouts.app')
@section('title', 'Cursos · Ikasgune')
@section('content')
<section class="container dashboard-section" aria-labelledby="courses-title">
    <p class="eyebrow">APRENDE A TU RITMO · IKASGUNE</p>
    <h1 id="courses-title">Encuentra tu próximo <em>curso.</em></h1>
    <p class="hero-description">Consulta los cursos disponibles. Para inscribirte necesitas una cuenta e iniciar sesión.</p>
    @auth
        <div class="hero-actions"><a class="text-link" href="{{ route('courses.mine') }}">Mis cursos →</a></div>
    @endauth
    <div class="feature-grid course-grid">
        @forelse($courses as $course)
            <article class="feature-card course-card">
                <span class="preview-label">INSCRIPCIÓN ABIERTA</span>
                <h2>{{ $course->title }}</h2>
                <p>{{ Str::limit($course->description, 170) }}</p>
                <a class="button" href="{{ route('courses.show', $course) }}" aria-label="Ver curso: {{ $course->title }}">Ver curso →</a>
            </article>
        @empty
            <div class="auth-card course-empty">
                <h2>Próximamente, nuevos cursos</h2>
                <p class="auth-description">Todavía no hay cursos publicados.</p>
                @can('access-admin')<a class="text-link" href="{{ route('admin.index') }}#crear-curso">Publicar el primer curso →</a>@endcan
            </div>
        @endforelse
    </div>
    <div class="admin-pagination">{{ $courses->links() }}</div>
</section>
@endsection
