@extends('layouts.app')
@section('title', $course->title.' · Ikasgune')
@section('content')
<section class="container dashboard-section" aria-labelledby="course-title">
    <a class="text-link" href="{{ route('courses.index') }}">← Todos los cursos</a>
    <div class="course-detail auth-card">
        <p class="eyebrow">CURSO · IKASGUNE</p>
        <h1 id="course-title">{{ $course->title }}</h1>
        @if(session('status'))<p class="auth-status" role="status">{{ session('status') }}</p>@endif
        <p class="course-description">{{ $course->description }}</p>
        @auth
            @if($isEnrolled)
                <p class="auth-status">Ya estás inscrito en este curso.</p>
                <div class="hero-actions"><a class="button" href="{{ route('courses.mine') }}">Ver mis cursos</a></div>
            @else
                <form method="POST" action="{{ route('enrollments.store', $course) }}" class="hero-actions">
                    @csrf
                    <button class="button" type="submit">Inscribirme en este curso</button>
                </form>
            @endif
        @else
            <p class="auth-description">Necesitas una cuenta para inscribirte. Si todavía no tienes una, podrás crearla desde la pantalla de acceso.</p>
            <div class="hero-actions"><a class="button" href="{{ route('courses.join', $course) }}">Acceder para inscribirme</a></div>
        @endauth
    </div>
</section>
@endsection
