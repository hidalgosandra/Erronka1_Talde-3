@extends('layouts.app')

@section('title', 'Crear cuenta · Ikasgune')

@section('content')
    <section class="container auth-section" aria-labelledby="register-title">
        <div class="auth-intro">
            <p class="eyebrow">TU PRIMER PASO · IKASGUNE</p>
            <h1 id="register-title">Un espacio para<br><em>crecer contigo.</em></h1>
            <p class="hero-description">Crea tu cuenta y empieza a formar parte de Ikasgune.</p>
            <a class="text-link" href="{{ route('inicio') }}">← Volver al inicio</a>
        </div>
        <div class="auth-card">
            <h2>Crear cuenta</h2>
            <p class="auth-description">Completa tus datos para empezar.</p>
            <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                @csrf
                <div class="form-field">
                    <label for="name">Nombre</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="255" autofocus @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')<p id="name-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="12" maxlength="72" aria-describedby="password-help @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror>
                    <p id="password-help" class="auth-description">Entre 12 y 72 caracteres.</p>
                    @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="password_confirmation">Repite la contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="12" maxlength="72">
                </div>
                <button class="button" type="submit">Crear mi cuenta <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-description">¿Ya tienes cuenta? <a class="text-link" href="{{ route('login') }}">Iniciar sesión</a></p>
        </div>
    </section>
@endsection
