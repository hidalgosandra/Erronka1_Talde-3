@extends('layouts.app')
@section('title', __('Cursos · Eskolak'))
@section('content')
<section class="container dashboard-section" aria-labelledby="courses-title">
    <p class="eyebrow">{{ __('APRENDE A TU RITMO · ESKOLAK') }}</p>
    <h1 id="courses-title">{{ __('Encuentra tu próximo') }} <em>{{ __('curso.') }}</em></h1>
    <p class="hero-description">{{ __('Consulta los cursos disponibles. Para inscribirte necesitas una cuenta e iniciar sesión.') }}</p>
    @auth
        <div class="hero-actions"><a class="text-link" href="{{ route('courses.mine') }}">{{ __('Mis cursos →') }}</a></div>
    @endauth
    <form class="catalog-filters auth-card" method="GET" action="{{ route('courses.index') }}">
        <div class="search-field"><label for="course-search">{{ __('Buscar cursos') }}</label><input id="course-search" name="q" value="{{ request('q') }}" placeholder="{{ __('Ej. programación, diseño...') }}" type="search"></div>
        <div class="filter-field"><label for="course-category">{{ __('Categoría') }}</label><select id="course-category" name="category"><option value="">{{ __('Todas') }}</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ __($category) }}</option>@endforeach</select></div>
        <div class="filter-field"><label for="course-level">{{ __('Nivel') }}</label><select id="course-level" name="level"><option value="">{{ __('Todos') }}</option>@foreach($levels as $level)<option value="{{ $level }}" @selected(request('level') === $level)>{{ __($level) }}</option>@endforeach</select></div>
        <div class="filter-field"><label for="course-sort">{{ __('Ordenar') }}</label><select id="course-sort" name="sort"><option value="">{{ __('Más recientes') }}</option><option value="popular" @selected(request('sort') === 'popular')>{{ __('Más populares') }}</option><option value="duration" @selected(request('sort') === 'duration')>{{ __('Duración') }}</option></select></div>
        <button class="button button-small" type="submit">{{ __('Aplicar filtros') }}</button>
    </form>
    <div class="feature-grid course-grid">
        @forelse($courses as $course)
            <article class="feature-card course-card">
                <span class="preview-label">{{ __($course->category) }} · {{ __($course->level) }}</span>
                <h2>{{ $course->localized('title') }}</h2>
                <p>{{ Str::limit($course->localized('description'), 170) }}</p>
                <span class="course-meta">{{ $course->duration_minutes }} {{ __('min ·') }} {{ $course->enrollments_count ?? $course->enrollments()->count() }} {{ __('personas inscritas') }}</span>
                <a class="button" href="{{ route('courses.show', $course) }}" aria-label="{{ __('Ver curso:') }} {{ $course->localized('title') }}">{{ __('Ver curso →') }}</a>
            </article>
        @empty
            <div class="auth-card course-empty">
                <h2>{{ __('Próximamente, nuevos cursos') }}</h2>
                <p class="auth-description">{{ __('Todavía no hay cursos publicados.') }}</p>
                @can('access-admin')<a class="text-link" href="{{ route('admin.index') }}#crear-curso">{{ __('Publicar el primer curso →') }}</a>@endcan
            </div>
        @endforelse
    </div>
    <div class="admin-pagination">{{ $courses->links() }}</div>
</section>
@endsection
