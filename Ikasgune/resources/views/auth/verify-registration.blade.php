@extends('layouts.app')

@section('title', 'Verificar cuenta · Ikasgune')

@section('content')
    <section class="container auth-section" aria-labelledby="verification-title">
        <div class="auth-intro">
            <p class="eyebrow">UN ÚLTIMO PASO · IKASGUNE</p>
            <h1 id="verification-title">Confirma tu<br><em>correo electrónico.</em></h1>
            <p class="hero-description">Hemos enviado un código de 6 dígitos a <strong>{{ session('registration.email') }}</strong>.</p>
            <a class="text-link" href="{{ route('register') }}">← Volver al registro</a>
        </div>
        <div class="auth-card">
            <h2>Introduce el código</h2>
            <p class="auth-description">El código es válido durante 10 minutos.</p>
            <form method="POST" action="{{ route('register.verify.store') }}" class="auth-form">
                @csrf
                <div class="form-field">
                    <label for="verification_code">Código de verificación</label>
                    <input id="verification_code" name="verification_code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus @error('verification_code') aria-invalid="true" aria-describedby="verification-code-error" @enderror>
                    @error('verification_code')<p id="verification-code-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <button class="button" type="submit">Verificar cuenta <span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-description">¿No lo has recibido? <a class="text-link" href="{{ route('register') }}">Solicitar otro código</a></p>
        </div>
    </section>
@endsection
