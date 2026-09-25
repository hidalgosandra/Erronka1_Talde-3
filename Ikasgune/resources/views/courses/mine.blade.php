@extends('layouts.app')
@section('title', __('Mis cursos · Eskolak'))
@section('content')
<section class="container dashboard-section" aria-labelledby="my-courses-title">
    <p class="eyebrow">{{ __('MI APRENDIZAJE · ESKOLAK') }}</p>
    <h1 id="my-courses-title">{{ __('Mis cursos') }}</h1>
    <a class="text-link" href="{{ route('courses.index') }}">{{ __('Explorar el catálogo →') }}</a>
    <div class="feature-grid course-grid">
        @forelse($enrollments as $enrollment)
            <article class="feature-card course-card">
                <span class="preview-label">{{ __('INSCRITO') }}</span>
                <h2>{{ $enrollment->course->localized('title') }}</h2>
                <p>{{ __('Inscripción:') }} {{ $enrollment->created_at->format('d/m/Y') }}</p>
                <a class="button" href="{{ route('courses.show', $enrollment->course) }}" aria-label="{{ __('Ver curso:') }} {{ $enrollment->course->localized('title') }}">{{ __('Ver curso →') }}</a>
            </article>
        @empty
            <div class="auth-card course-empty"><h2>{{ __('Tu próximo paso empieza aquí') }}</h2><p class="auth-description">{{ __('Todavía no te has inscrito en ningún curso. Explora el catálogo y elige uno.') }}</p></div>
        @endforelse
    </div>
    <div class="admin-pagination">{{ $enrollments->links() }}</div>
</section>
@endsection
