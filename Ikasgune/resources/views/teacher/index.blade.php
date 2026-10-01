@extends('layouts.app')

@section('title', __('Espacio docente · Eskolak'))

@section('content')
    @if($errors->any())
        <div class="container auth-status" role="alert">
            <strong>{{ __('No se han guardado los cambios:') }}</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <section class="container dashboard-section" aria-labelledby="teacher-title">
        <p class="eyebrow">{{ __('ESKOLAK · DOCENCIA') }}</p>
        <h1 id="teacher-title">{{ __('Espacio docente') }}</h1>
        <p class="hero-description">{{ __('Gestiona las matrículas y los materiales de tus cursos.') }}</p>
        @include('teacher.charts')
        <div class="teacher-course-list">
            @forelse($courses as $course)
                @php($availableStudents = $students->whereNotIn('id', $course->enrollments->pluck('user_id')))
                <article class="auth-card teacher-course">
                    <div class="teacher-course-heading">
                        <div><p class="eyebrow">{{ __($course->category) }} · {{ __($course->level) }}</p><h2>{{ $course->localized('title') }}</h2></div>
                        <span class="teacher-enrollment-count">{{ $course->enrollments->count() }} {{ __('alumnos') }}</span>
                    </div>
                    <p class="course-description">{{ $course->localized('description') }}</p>
                    <div class="teacher-course-grid">
                        <section class="teacher-tools" aria-labelledby="students-{{ $course->id }}">
                            <h3 id="students-{{ $course->id }}">{{ __('Alumnos inscritos') }}</h3>
                            @forelse($course->enrollments as $enrollment)
                                <div class="teacher-student-row">
                                    <div><strong>{{ $enrollment->user->name }}</strong><span class="table-subtext">{{ $enrollment->user->email }}</span></div>
                                    <form method="POST" action="{{ route('teacher.students.destroy', [$course, $enrollment->user]) }}" data-confirm-delete="{{ __('¿Desvincular a este alumno del curso?') }}">
                                        @csrf @method('DELETE')
                                        <button class="text-link danger-link" type="submit">{{ __('Desvincular') }}</button>
                                    </form>
                                </div>
                            @empty
                                <p class="auth-description">{{ __('Aún no hay alumnos inscritos.') }}</p>
                            @endforelse
                            <form class="teacher-inline-form" method="POST" action="{{ route('teacher.students.store', $course) }}">
                                @csrf
                                <label for="student-{{ $course->id }}">{{ __('Vincular alumno') }}</label>
                                <div class="teacher-form-row">
                                    <select id="student-{{ $course->id }}" name="user_id" required @disabled($availableStudents->isEmpty())>
                                        <option value="">{{ $availableStudents->isEmpty() ? __('No hay alumnos disponibles') : __('Selecciona un alumno') }}</option>
                                        @foreach($availableStudents as $student)<option value="{{ $student->id }}">{{ $student->name }} · {{ $student->email }}</option>@endforeach
                                    </select>
                                    <button class="button button-small" type="submit" @disabled($availableStudents->isEmpty())>{{ __('Vincular') }}</button>
                                </div>
                            </form>
                        </section>
                        <section class="teacher-tools" aria-labelledby="materials-{{ $course->id }}">
                            <h3 id="materials-{{ $course->id }}">{{ __('Material del curso') }}</h3>
                            @forelse($course->materials as $material)
                                <div class="teacher-material-row">
                                    <div><a class="text-link" href="{{ route('materials.download', $material) }}">{{ $material->title }}</a><span class="table-subtext">{{ $material->original_name }}</span></div>
                                    <form method="POST" action="{{ route('teacher.materials.destroy', [$course, $material]) }}" data-confirm-delete="{{ __('¿Eliminar este material del curso?') }}">
                                        @csrf @method('DELETE')
                                        <button class="text-link danger-link" type="submit">{{ __('Eliminar') }}</button>
                                    </form>
                                </div>
                            @empty
                                <p class="auth-description">{{ __('Todavía no has añadido material.') }}</p>
                            @endforelse
                            <form class="teacher-inline-form" method="POST" action="{{ route('teacher.materials.store', $course) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-field"><label for="material-title-{{ $course->id }}">{{ __('Nombre del material') }}</label><input id="material-title-{{ $course->id }}" name="title" maxlength="120" required></div>
                                <div class="form-field"><label for="material-file-{{ $course->id }}">{{ __('Archivo (máximo 5 MB)') }}</label><input id="material-file-{{ $course->id }}" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.webp" required></div>
                                <button class="button button-small" type="submit">{{ __('Añadir material') }}</button>
                            </form>
                        </section>
                    </div>
                </article>
            @empty
                <div class="auth-card"><h2>{{ __('Aún no tienes cursos asignados.') }}</h2><p class="auth-description">{{ __('Pide al administrador que te asigne un curso.') }}</p></div>
            @endforelse
        </div>
    </section>
@endsection
