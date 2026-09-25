@extends('layouts.app')

@section('title', __('Crear cuenta · Eskolak'))

@section('content')
    <section class="container auth-section" aria-labelledby="register-title">
        <div class="auth-intro">
            <p class="eyebrow">{{ __('TU PRIMER PASO · ESKOLAK') }}</p>
            <h1 id="register-title">{{ __('Un espacio para') }}<br><em>{{ __('crecer contigo.') }}</em></h1>
            <p class="hero-description">{{ __('Crea tu cuenta y empieza a formar parte de Eskolak.') }}</p>
            <a class="text-link" href="{{ route('inicio') }}">{{ __('← Volver al inicio') }}</a>
        </div>
        <div class="auth-card">
            <h2>{{ __('Crear cuenta') }}</h2>
            <p class="auth-description">{{ __('Completa tus datos para empezar.') }}</p>
            <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                @csrf
                <div class="form-field">
                    <label for="name">{{ __('Nombre') }}</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="255" autofocus @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')<p id="name-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="email">{{ __('Correo electrónico') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="password">{{ __('Contraseña') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="12" maxlength="72" aria-describedby="password-help @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror>
                    <p id="password-help" class="auth-description">{{ __('Entre 12 y 72 caracteres.') }}</p>
                    @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="password_confirmation">{{ __('Repite la contraseña') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="12" maxlength="72">
                </div>
                <button class="button" type="submit">{{ __('Crear mi cuenta') }} <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-description">{{ __('¿Ya tienes cuenta?') }} <a class="text-link" href="{{ route('login') }}">{{ __('Iniciar sesión') }}</a></p>
        </div>
    </section>
@endsection
