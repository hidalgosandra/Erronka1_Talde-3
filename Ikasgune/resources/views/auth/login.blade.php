@extends('layouts.app')

@section('title', 'Iniciar sesión · Ikasgune')

@section('content')
    <section class="container auth-section" aria-labelledby="login-title">
        <div class="auth-intro">
            <p class="eyebrow">ONGI ETORRI BERRIRO · BIENVENIDO/A</p>
            <h1 id="login-title">Tu espacio.<br>Tu siguiente <em>paso.</em></h1>
            <p class="hero-description">Accede a tu cuenta de Ikasgune y sigue construyendo lo que viene.</p>
            <a class="text-link" href="{{ route('inicio') }}">← Volver al inicio</a>
        </div>
        <div class="auth-card">
            <h2>Iniciar sesión</h2>
            <p class="auth-description">Introduce el correo y la contraseña de tu cuenta.</p>
            @if(session('status'))
                <p class="auth-status" role="status">{{ session('status') }}</p>
            @endif
            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf
                <div class="form-field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required maxlength="255" autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p id="email-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    @error('password')<p id="password-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <label class="checkbox-field"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Recordarme en este dispositivo</label>
                @error('remember')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                <button class="button" type="submit">Entrar en mi espacio <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-description">¿Todavía no tienes cuenta? <a class="text-link" href="{{ route('register') }}">Regístrate</a></p>
        </div>
    </section>
@endsection
