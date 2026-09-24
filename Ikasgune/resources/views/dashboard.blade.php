@extends('layouts.app')

@section('title', 'Mi espacio · Ikasgune')

@section('content')
    <section class="container dashboard-section" aria-labelledby="dashboard-title">
        <p class="eyebrow">MI ESPACIO · IKASGUNE</p>
        <h1 id="dashboard-title">Hola, {{ auth()->user()->name }}.</h1>
        <p class="hero-description">Has iniciado sesión correctamente. Este es tu espacio personal.</p>
        <div class="auth-card account-card">
            <h2>Tu cuenta</h2>
            <dl><dt>Nombre</dt><dd>{{ auth()->user()->name }}</dd><dt>Correo electrónico</dt><dd>{{ auth()->user()->email }}</dd></dl>
            <div class="hero-actions"><a class="button" href="{{ route('courses.mine') }}">Mis cursos</a><a class="text-link" href="{{ route('courses.index') }}">Ver catálogo →</a></div>
        </div>
    </section>
@endsection
