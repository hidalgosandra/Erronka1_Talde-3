<section class="auth-card">
    <h2>{{ __('Crear clase') }}</h2>
    <p class="auth-description">{{ __('Los alumnos verán los cursos de los profesores asignados a su clase.') }}</p>
    <form class="auth-form" method="POST" action="{{ route('admin.classes.store') }}">
        @csrf
        <input type="hidden" name="_admin_tab" value="admin-classes">
        <div class="form-field"><label for="class-name">{{ __('Nombre de la clase') }}</label><input id="class-name" name="name" value="{{ old('name') }}" maxlength="80" placeholder="2º DAW A" required></div>
        <fieldset class="class-teachers"><legend>{{ __('Profesores') }}</legend>
            @forelse($teachers as $teacher)
                <label class="checkbox-field"><input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}" @checked(in_array($teacher->id, old('teacher_ids', [])))>{{ $teacher->name }}</label>
            @empty<p class="auth-description">{{ __('Primero asigna el rol de profesor a un usuario.') }}</p>@endforelse
        </fieldset>
        <button class="button" type="submit">{{ __('Crear clase') }}</button>
    </form>
</section>
<div class="admin-record-grid">
    @foreach($classes as $schoolClass)
        <section class="auth-card">
            <h2>{{ $schoolClass->name }}</h2>
            <p class="auth-description">{{ $schoolClass->students_count }} {{ __('alumnos') }}</p>
            <form class="auth-form" method="POST" action="{{ route('admin.classes.update', $schoolClass) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_admin_tab" value="admin-classes">
                <div class="form-field"><label for="class-name-{{ $schoolClass->id }}">{{ __('Nombre de la clase') }}</label><input id="class-name-{{ $schoolClass->id }}" name="name" value="{{ $schoolClass->name }}" maxlength="80" required></div>
                <fieldset class="class-teachers"><legend>{{ __('Profesores') }}</legend>
                    @foreach($teachers as $teacher)
                        <label class="checkbox-field"><input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}" @checked($schoolClass->teachers->contains($teacher))>{{ $teacher->name }}</label>
                    @endforeach
                </fieldset>
                <button class="button button-small" type="submit">{{ __('Guardar cambios') }}</button>
            </form>
        </section>
    @endforeach
</div>
