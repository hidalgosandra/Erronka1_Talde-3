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
    <form class="catalog-filters auth-card" method="GET" action="{{ route('courses.index') }}">
        <div class="search-field"><label for="course-search">Buscar cursos</label><input id="course-search" name="q" value="{{ request('q') }}" placeholder="Ej. programación, diseño..." type="search"></div>
        <div class="filter-field"><label for="course-category">Categoría</label><select id="course-category" name="category"><option value="">Todas</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></div>
        <div class="filter-field"><label for="course-level">Nivel</label><select id="course-level" name="level"><option value="">Todos</option>@foreach($levels as $level)<option value="{{ $level }}" @selected(request('level') === $level)>{{ $level }}</option>@endforeach</select></div>
        <div class="filter-field"><label for="course-sort">Ordenar</label><select id="course-sort" name="sort"><option value="">Más recientes</option><option value="popular" @selected(request('sort') === 'popular')>Más populares</option><option value="duration" @selected(request('sort') === 'duration')>Duración</option></select></div>
        <button class="button button-small" type="submit">Aplicar filtros</button>
    </form>
    <div class="feature-grid course-grid">
        @forelse($courses as $course)
            <article class="feature-card course-card">
                <span class="preview-label">{{ $course->category }} · {{ $course->level }}</span>
                <h2>{{ $course->title }}</h2>
                <p>{{ Str::limit($course->description, 170) }}</p>
                <span class="course-meta">{{ $course->duration_minutes }} min · {{ $course->enrollments_count ?? $course->enrollments()->count() }} personas inscritas</span>
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
