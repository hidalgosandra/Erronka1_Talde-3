@extends('layouts.app')
@section('title', 'Mis cursos · Ikasgune')
@section('content')
<section class="container dashboard-section" aria-labelledby="my-courses-title">
    <p class="eyebrow">MI APRENDIZAJE · IKASGUNE</p>
    <h1 id="my-courses-title">Mis cursos</h1>
    <a class="text-link" href="{{ route('courses.index') }}">Explorar el catálogo →</a>
    <div class="feature-grid course-grid">
        @forelse($enrollments as $enrollment)
            <article class="feature-card course-card">
                <span class="preview-label">INSCRITO</span>
                <h2>{{ $enrollment->course->title }}</h2>
                <p>Inscripción: {{ $enrollment->created_at->format('d/m/Y') }}</p>
                <a class="button" href="{{ route('courses.show', $enrollment->course) }}" aria-label="Ver curso: {{ $enrollment->course->title }}">Ver curso →</a>
            </article>
        @empty
            <div class="auth-card course-empty"><h2>Tu próximo paso empieza aquí</h2><p class="auth-description">Todavía no te has inscrito en ningún curso. Explora el catálogo y elige uno.</p></div>
        @endforelse
    </div>
    <div class="admin-pagination">{{ $enrollments->links() }}</div>
</section>
@endsection
