<div class="admin-stats">
    <article class="auth-card"><h2>{{ __('Mis cursos') }}</h2><p class="stat-value">{{ $courses->count() }}</p></article>
    <article class="auth-card"><h2>{{ __('Alumnos inscritos') }}</h2><p class="stat-value">{{ $courses->flatMap->enrollments->pluck('user_id')->unique()->count() }}</p></article>
    <article class="auth-card"><h2>{{ __('Material del curso') }}</h2><p class="stat-value">{{ $courses->sum(fn ($course) => $course->materials->count()) }}</p></article>
</div>
<div class="admin-charts">
    @foreach(['enrollments' => __('Inscripciones por curso'), 'materials' => __('Materiales por curso')] as $relation => $title)
        <section class="auth-card chart-panel" aria-labelledby="teacher-chart-{{ $relation }}">
            <h2 id="teacher-chart-{{ $relation }}">{{ $title }}</h2>
            @php($maximum = max(1, $courses->max(fn ($course) => $course->$relation->count()) ?? 0))
            <div class="chart-rows">
                @forelse($courses as $course)
                    @php($count = $course->$relation->count())
                    <div class="chart-row">
                        <div class="chart-label"><span>{{ $course->localized('title') }}</span><strong>{{ $count }}</strong></div>
                        <div class="chart-track {{ $relation === 'materials' ? 'chart-track-green' : '' }}" role="meter" aria-label="{{ $course->localized('title') }}" aria-valuemin="0" aria-valuemax="{{ $maximum }}" aria-valuenow="{{ $count }}"><span style="width: {{ round($count / $maximum * 100) }}%"></span></div>
                    </div>
                @empty<p class="auth-description">{{ __('Aún no tienes cursos asignados.') }}</p>@endforelse
            </div>
        </section>
    @endforeach
</div>
