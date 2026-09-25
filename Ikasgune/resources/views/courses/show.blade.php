@extends('layouts.app')
@section('title', $course->localized('title').' · Eskolak')
@section('content')
<section class="container dashboard-section" aria-labelledby="course-title">
    <a class="text-link" href="{{ route('courses.index') }}">{{ __('← Todos los cursos') }}</a>
    <div class="course-detail auth-card">
        <p class="eyebrow">{{ __('CURSO · ESKOLAK') }}</p>
        <h1 id="course-title">{{ $course->localized('title') }}</h1>
        <div class="course-facts"><span>{{ __($course->category) }}</span><span>{{ __($course->level) }}</span><span>{{ $course->duration_minutes }} {{ __('min') }}</span></div>
        @if(session('status'))<p class="auth-status" role="status">{{ session('status') }}</p>@endif
        <p class="course-description">{{ $course->localized('description') }}</p>
        @auth
            @if($isEnrolled)
                <p class="auth-status">{{ __('Ya estás inscrito en este curso.') }}</p>
                <div class="hero-actions"><a class="button" href="{{ route('courses.mine') }}">{{ __('Ver mis cursos') }}</a></div>
            @else
                <form method="POST" action="{{ route('enrollments.store', $course) }}" class="hero-actions">
                    @csrf
                    <button class="button" type="submit">{{ __('Inscribirme en este curso') }}</button>
                </form>
            @endif
        @else
            <p class="auth-description">{{ __('Necesitas una cuenta para inscribirte. Si todavía no tienes una, podrás crearla desde la pantalla de acceso.') }}</p>
            <div class="hero-actions"><a class="button" href="{{ route('courses.join', $course) }}">{{ __('Acceder para inscribirme') }}</a></div>
        @endauth
    </div>
</section>
@endsection
