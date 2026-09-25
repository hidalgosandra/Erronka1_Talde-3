@extends('layouts.app')
@section('title', __('Nueva contraseña · Eskolak'))
@section('content')
<section class="container auth-section">
    <div class="auth-intro"><p class="eyebrow">{{ __('TU CUENTA · ESKOLAK') }}</p><h1>{{ __('Un nuevo') }} <em>{{ __('comienzo.') }}</em></h1><p class="hero-description">{{ __('Elige una contraseña de entre 12 y 72 caracteres.') }}</p></div>
    <div class="auth-card">
        <h2>{{ __('Nueva contraseña') }}</h2>
        @if($errors->any())<div class="field-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="auth-form" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-field"><label for="email">{{ __('Correo electrónico') }}</label><input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required></div>
            <div class="form-field"><label for="password">{{ __('Nueva contraseña') }}</label><input id="password" type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" required></div>
            <div class="form-field"><label for="password_confirmation">{{ __('Repite la contraseña') }}</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" maxlength="72" required></div>
            <button class="button" type="submit">{{ __('Guardar contraseña') }}</button>
        </form>
        <p class="auth-description"><a class="text-link" href="{{ route('password.request') }}">{{ __('Solicitar otro enlace') }}</a></p>
    </div>
</section>
@endsection
