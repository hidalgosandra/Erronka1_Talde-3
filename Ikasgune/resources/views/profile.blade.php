@extends('layouts.app')
@section('title', __('Perfil académico'))
@section('content')
<section class="container dashboard-section profile-section">
    <div class="auth-card">
        <a class="text-link" href="{{ route('dashboard') }}">{{ __('← Volver a Mi espacio') }}</a>
        <h1>{{ __('Mis datos') }}</h1>
        <p class="auth-description">{{ __('Actualiza tus datos personales y tus preferencias de aprendizaje.') }}</p>
        @if($errors->any())
            <div class="field-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if($classes->isEmpty())
            <p class="auth-status">{{ __('El administrador todavía no ha creado las clases. Pídele que añada la tuya.') }}</p>
        @else
            <form class="auth-form" method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="form-field"><label for="name">{{ __('Nombre') }}</label><input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required></div>
                <div class="form-field"><label for="email">{{ __('Correo electrónico') }}</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required></div>
                <div class="form-field"><label for="phone">{{ __('Teléfono') }}</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30"></div>
                <div class="form-field"><label for="address">{{ __('Dirección') }}</label><input id="address" name="address" value="{{ old('address', $user->address) }}" maxlength="255"></div>
                <div class="form-field">
                    <label for="birth_date">{{ __('Fecha de nacimiento') }}</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" min="{{ now()->subYears(120)->toDateString() }}" max="{{ now()->subDay()->toDateString() }}">
                </div>
                @if($user->isStudent())
                    <div class="form-field">
                    <label for="school_class_id">{{ __('Clase') }}</label>
                    <select id="school_class_id" name="school_class_id" required>
                        <option value="">{{ __('Selecciona tu clase') }}</option>
                        @foreach($classes as $schoolClass)
                            <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id', $user->school_class_id) === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>
                        @endforeach
                    </select>
                    </div>
                @endif
                <div class="form-field"><label for="password">{{ __('Nueva contraseña (opcional)') }}</label><input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password"><p class="auth-description">{{ __('Déjalo vacío si no quieres cambiarla.') }}</p></div>
                <div class="form-field"><label for="password_confirmation">{{ __('Repite la nueva contraseña') }}</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password"></div>
                <button class="button" type="submit">{{ __('Guardar cambios') }}</button>
            </form>
        @endif
    </div>
</section>
@endsection
