@extends('layouts.app')
@section('title', __('Perfil académico'))
@section('content')
<section class="container dashboard-section profile-section">
    <div class="auth-card">
        <h1>{{ __('Perfil académico') }}</h1>
        <p class="auth-description">{{ __('Completa tus datos para ver los cursos de los profesores de tu clase.') }}</p>
        @if($errors->any())
            <div class="field-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if($classes->isEmpty())
            <p class="auth-status">{{ __('El administrador todavía no ha creado las clases. Pídele que añada la tuya.') }}</p>
        @else
            <form class="auth-form" method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="form-field">
                    <label for="birth_date">{{ __('Fecha de nacimiento') }}</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" min="{{ now()->subYears(120)->toDateString() }}" max="{{ now()->subDay()->toDateString() }}" required>
                </div>
                <div class="form-field">
                    <label for="school_class_id">{{ __('Clase') }}</label>
                    <select id="school_class_id" name="school_class_id" required>
                        <option value="">{{ __('Selecciona tu clase') }}</option>
                        @foreach($classes as $schoolClass)
                            <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id', $user->school_class_id) === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="button" type="submit">{{ __('Guardar y ver mis cursos') }}</button>
            </form>
        @endif
    </div>
</section>
@endsection
