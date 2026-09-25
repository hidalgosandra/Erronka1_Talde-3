@extends('layouts.app')

@section('title', __('Verificar cuenta · Eskolak'))

@section('content')
    <section class="container auth-section" aria-labelledby="verification-title">
        <div class="auth-intro">
            <p class="eyebrow">{{ __('UN ÚLTIMO PASO · ESKOLAK') }}</p>
            <h1 id="verification-title">{{ __('Confirma tu') }}<br><em>{{ __('correo electrónico.') }}</em></h1>
            <p class="hero-description">{{ __('Hemos enviado un código de 6 dígitos a') }} <strong>{{ session('registration.email') }}</strong>.</p>
            <a class="text-link" href="{{ route('register') }}">{{ __('← Volver al registro') }}</a>
        </div>
        <div class="auth-card">
            <h2>{{ __('Introduce el código') }}</h2>
            <p class="auth-description">{{ __('El código es válido durante 10 minutos.') }}</p>
            <form method="POST" action="{{ route('register.verify.store') }}" class="auth-form">
                @csrf
                <div class="form-field">
                    <label for="verification_code">{{ __('Código de verificación') }}</label>
                    <input id="verification_code" name="verification_code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus @error('verification_code') aria-invalid="true" aria-describedby="verification-code-error" @enderror>
                    @error('verification_code')<p id="verification-code-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <button class="button" type="submit">{{ __('Verificar cuenta') }} <span aria-hidden="true">→</span></button>
            </form>
            <form method="POST" action="{{ route('register.resend') }}" class="hero-actions">
                @csrf
                <button class="button button-secondary" type="submit">{{ __('Reenviar código') }}</button>
            </form>
            <p class="auth-description">{{ __('Puedes solicitar un nuevo código una vez por minuto.') }}</p>
        </div>
    </section>
@endsection
