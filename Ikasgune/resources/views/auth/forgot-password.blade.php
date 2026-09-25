@extends('layouts.app')
@section('title', __('Recuperar contraseña · Eskolak'))
@section('content')
<section class="container auth-section">
    <div class="auth-intro"><p class="eyebrow">{{ __('RECUPERA TU ACCESO') }}</p><h1>{{ __('Vuelve a tu') }} <em>{{ __('espacio.') }}</em></h1><p class="hero-description">{{ __('Te enviaremos un enlace para elegir una nueva contraseña.') }}</p></div>
    <div class="auth-card">
        <h2>{{ __('Recuperar contraseña') }}</h2>
        <form class="auth-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-field"><label for="email">{{ __('Correo electrónico') }}</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<p class="field-error" id="email-error" role="alert">{{ $message }}</p>@enderror</div>
            <button class="button" type="submit">{{ __('Enviar enlace') }}</button>
        </form>
        <p class="auth-description"><a class="text-link" href="{{ route('login') }}">{{ __('Volver al inicio de sesión') }}</a></p>
    </div>
</section>
@endsection
