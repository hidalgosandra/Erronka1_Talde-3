@extends('layouts.app')

@section('title', __('Administración · Eskolak'))

@section('content')
    @php
        $activeTab = old('_admin_tab', request('tab', 'admin-overview'));
        if (! in_array($activeTab, ['admin-overview', 'admin-courses', 'admin-users', 'admin-classes'], true)) {
            $activeTab = 'admin-overview';
        }
    @endphp
    @if($errors->any())
        <div class="container auth-status" role="alert">
            <strong>{{ __('No se han guardado los cambios:') }}</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <section class="container dashboard-section admin-dashboard" aria-labelledby="admin-title">
        <p class="eyebrow">{{ __('ESKOLAK · ADMINISTRACIÓN') }}</p>
        <h1 id="admin-title">{{ __('Panel de administración') }}</h1>
        <p class="hero-description">{{ __('Gestiona cursos y alumnos desde un único espacio privado.') }}</p>
        <div class="admin-tabs" role="tablist" aria-label="{{ __('Secciones de administración') }}">
            <a class="admin-tab {{ $activeTab === 'admin-classes' ? 'is-active' : '' }}" id="tab-admin-classes" href="{{ route('admin.index', ['tab' => 'admin-classes']) }}" role="tab" tabindex="{{ $activeTab === 'admin-classes' ? '0' : '-1' }}" aria-selected="{{ $activeTab === 'admin-classes' ? 'true' : 'false' }}" aria-controls="admin-classes" data-admin-tab="admin-classes">{{ __('Clases') }}</a>
            <a class="admin-tab {{ $activeTab === 'admin-overview' ? 'is-active' : '' }}" id="tab-admin-overview" href="{{ route('admin.index', ['tab' => 'admin-overview']) }}" role="tab" tabindex="{{ $activeTab === 'admin-overview' ? '0' : '-1' }}" aria-selected="{{ $activeTab === 'admin-overview' ? 'true' : 'false' }}" aria-controls="admin-overview" data-admin-tab="admin-overview">{{ __('Resumen') }}</a>
            <a class="admin-tab {{ $activeTab === 'admin-courses' ? 'is-active' : '' }}" id="tab-admin-courses" href="{{ route('admin.index', ['tab' => 'admin-courses']) }}" role="tab" tabindex="{{ $activeTab === 'admin-courses' ? '0' : '-1' }}" aria-selected="{{ $activeTab === 'admin-courses' ? 'true' : 'false' }}" aria-controls="admin-courses" data-admin-tab="admin-courses">{{ __('Cursos') }}</a>
            <a class="admin-tab {{ $activeTab === 'admin-users' ? 'is-active' : '' }}" id="tab-admin-users" href="{{ route('admin.index', ['tab' => 'admin-users']) }}" role="tab" tabindex="{{ $activeTab === 'admin-users' ? '0' : '-1' }}" aria-selected="{{ $activeTab === 'admin-users' ? 'true' : 'false' }}" aria-controls="admin-users" data-admin-tab="admin-users">{{ __('Alumnos') }}</a>
        </div>
        <div id="admin-overview" class="admin-panel {{ $activeTab === 'admin-overview' ? 'is-active' : '' }}" role="tabpanel" aria-labelledby="tab-admin-overview" @if($activeTab !== 'admin-overview') hidden @endif>
        <div class="admin-stats">
            <article class="auth-card"><h2>{{ __('Usuarios') }}</h2><p class="stat-value">{{ $totalUsers }}</p></article>
            <article class="auth-card"><h2>{{ __('Administradores') }}</h2><p class="stat-value">{{ $totalAdmins }}</p></article>
            <article class="auth-card"><h2>{{ __('Cuentas normales') }}</h2><p class="stat-value">{{ $totalUsers - $totalAdmins }}</p></article>
        </div>
        <div class="admin-charts">
            <section class="auth-card chart-panel" aria-labelledby="class-chart-title">
                <h2 id="class-chart-title">{{ __('Alumnos por clase') }}</h2>
                @php($maxClassStudents = max(1, (int) $classes->max('students_count')))
                <div class="chart-rows">
                    @forelse($classes as $schoolClass)
                        <div class="chart-row">
                            <div class="chart-label"><span>{{ $schoolClass->name }}</span><strong>{{ $schoolClass->students_count }}</strong></div>
                            <div class="chart-track chart-track-green" role="meter" aria-label="{{ $schoolClass->name }}" aria-valuemin="0" aria-valuemax="{{ $maxClassStudents }}" aria-valuenow="{{ $schoolClass->students_count }}"><span style="width: {{ round($schoolClass->students_count / $maxClassStudents * 100) }}%"></span></div>
                        </div>
                    @empty<p class="auth-description">{{ __('Crea una clase para empezar.') }}</p>@endforelse
                </div>
            </section>
            <section class="auth-card chart-panel" aria-labelledby="roles-chart-title">
                <p class="eyebrow">{{ __('USUARIOS') }}</p>
                <h2 id="roles-chart-title">{{ __('Distribución por rol') }}</h2>
                <div class="chart-rows">
                    @foreach($roleChart as $role)
                        @php($rolePercent = $totalUsers > 0 ? round($role['count'] / $totalUsers * 100) : 0)
                        <div class="chart-row">
                            <div class="chart-label"><span>{{ $role['label'] }}</span><strong>{{ $role['count'] }}</strong></div>
                            <div class="chart-track" role="meter" aria-label="{{ $role['label'] }}" aria-valuemin="0" aria-valuemax="{{ max(1, $totalUsers) }}" aria-valuenow="{{ $role['count'] }}"><span style="width: {{ $rolePercent }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="auth-card chart-panel" aria-labelledby="enrollments-chart-title">
                <p class="eyebrow">{{ __('MATRÍCULAS') }}</p>
                <h2 id="enrollments-chart-title">{{ __('Inscripciones por curso') }}</h2>
                @php($maxEnrollments = max(1, (int) $enrollmentChart->max('enrollments_count')))
                <div class="chart-rows">
                    @forelse($enrollmentChart as $course)
                        @php($enrollmentPercent = round($course->enrollments_count / $maxEnrollments * 100))
                        <div class="chart-row">
                            <div class="chart-label"><span>{{ $course->localized('title') }}</span><strong>{{ $course->enrollments_count }}</strong></div>
                            <div class="chart-track chart-track-green" role="meter" aria-label="{{ __('Inscripciones en') }} {{ $course->localized('title') }}" aria-valuemin="0" aria-valuemax="{{ $maxEnrollments }}" aria-valuenow="{{ $course->enrollments_count }}"><span style="width: {{ $enrollmentPercent }}%"></span></div>
                        </div>
                    @empty
                        <p class="auth-description">{{ __('Todavía no hay cursos publicados.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
        </div>
        <div id="admin-classes" class="admin-panel {{ $activeTab === 'admin-classes' ? 'is-active' : '' }}" role="tabpanel" aria-labelledby="tab-admin-classes" @if($activeTab !== 'admin-classes') hidden @endif>
            @include('admin.classes')
        </div>
        <div id="admin-courses" class="admin-panel {{ $activeTab === 'admin-courses' ? 'is-active' : '' }}" role="tabpanel" aria-labelledby="tab-admin-courses" @if($activeTab !== 'admin-courses') hidden @endif>
        <section id="crear-curso" class="auth-card course-create" aria-labelledby="create-course-title">
            <h2 id="create-course-title">{{ __('Crear un curso') }}</h2>
            <p class="auth-description">{{ __('Aparecerá en el catálogo y los usuarios podrán inscribirse.') }}</p>
            <form class="auth-form" method="POST" action="{{ route('admin.courses.store') }}"><input type="hidden" name="_admin_tab" value="admin-courses">
                @csrf
                <div class="form-field">
                    <label for="title">{{ __('Título') }}</label>
                    <input id="title" name="title" value="{{ old('title') }}" maxlength="255" required @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
                    @error('title')<p id="title-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="description">{{ __('Descripción') }}</label>
                    <textarea id="description" name="description" rows="4" maxlength="10000" required @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                    @error('description')<p id="description-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-grid">
                    <div class="form-field"><label for="category">{{ __('Categoría') }}</label><input id="category" name="category" value="{{ old('category', 'General') }}" maxlength="80" required></div>
                    <div class="form-field"><label for="level">{{ __('Nivel') }}</label><select id="level" name="level"><option value="Todos los niveles">{{ __('Todos los niveles') }}</option><option value="Inicial">{{ __('Inicial') }}</option><option value="Intermedio">{{ __('Intermedio') }}</option><option value="Avanzado">{{ __('Avanzado') }}</option></select></div>
                    <div class="form-field"><label for="duration_minutes">{{ __('Duración (minutos)') }}</label><input id="duration_minutes" name="duration_minutes" type="number" min="15" max="1000" value="{{ old('duration_minutes', 60) }}" required></div>
                    <label class="checkbox-field"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))> {{ __('Mostrar en destacados') }}</label>
                </div>
                <div class="form-field"><label for="new-course-teacher">{{ __('Profesor responsable') }}</label><select id="new-course-teacher" name="teacher_id"><option value="">{{ __('Sin asignar') }}</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></div>
                <div><button class="button" type="submit">{{ __('Publicar curso') }}</button> <a class="text-link" href="{{ route('courses.index') }}">{{ __('Ver catálogo →') }}</a></div>
            </form>
        </section>
        <section class="auth-card admin-users" aria-labelledby="courses-title">
            <h2 id="courses-title">{{ __('Cursos publicados') }}</h2>
            <div class="admin-record-grid">
                @forelse($courses as $course)
                    <article class="record-card">
                        <div><span class="preview-label">{{ __($course->category) }} · {{ __($course->level) }}</span><h3>{{ $course->title }}</h3><p>{{ Str::limit($course->description, 100) }}</p><p>{{ __('Profesor:') }} {{ $course->teacher?->name ?? __('Sin asignar') }}</p></div>
                        <details><summary>{{ __('Editar curso') }}</summary>
                            <form class="auth-form" method="POST" action="{{ route('admin.courses.update', $course) }}"><input type="hidden" name="_admin_tab" value="admin-courses">
                                @csrf @method('PUT')
                                <div class="form-field"><label for="course-title-{{ $course->id }}">{{ __('Izenburua') }}</label><input id="course-title-{{ $course->id }}" name="title" value="{{ $course->title }}" required></div>
                                <div class="form-field"><label for="course-description-{{ $course->id }}">{{ __('Deskribapena') }}</label><textarea id="course-description-{{ $course->id }}" name="description" rows="3" required>{{ $course->description }}</textarea></div>
                                <div class="form-grid"><input name="category" value="{{ __($course->category) }}" aria-label="{{ __('Kategoria') }}" required><select name="level" aria-label="{{ __('Maila') }}"><option @selected($course->level === 'Todos los niveles')>{{ __('Todos los niveles') }}</option><option @selected($course->level === 'Inicial')>{{ __('Inicial') }}</option><option @selected($course->level === 'Intermedio')>{{ __('Intermedio') }}</option><option @selected($course->level === 'Avanzado')>{{ __('Avanzado') }}</option></select><input name="duration_minutes" type="number" min="15" value="{{ $course->duration_minutes }}" aria-label="{{ __('Iraupena') }}" required></div>
                                <div class="form-field"><label for="course-teacher-{{ $course->id }}">{{ __('Profesor responsable') }}</label><select id="course-teacher-{{ $course->id }}" name="teacher_id"><option value="">{{ __('Sin asignar') }}</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($course->teacher_id === $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></div>
                                <label class="checkbox-field"><input type="checkbox" name="is_featured" value="1" @checked($course->is_featured)> {{ __('Nabarmendua') }}</label>
                                <button class="button button-small" type="submit">{{ __('Guardar cambios') }}</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" data-confirm-delete="{{ __('¿Seguro que quieres eliminar este registro?') }}"><input type="hidden" name="_admin_tab" value="admin-courses">@csrf @method('DELETE')<button class="text-link danger-link" type="submit">{{ __('Eliminar') }}</button></form>
                    </article>
                @empty
                    <p class="auth-description">{{ __('Todavía no hay cursos publicados.') }}</p>
                @endforelse
            </div>
        </section>
        </div>
        <div id="admin-users" class="admin-panel {{ $activeTab === 'admin-users' ? 'is-active' : '' }}" role="tabpanel" aria-labelledby="tab-admin-users" @if($activeTab !== 'admin-users') hidden @endif>
        <section class="auth-card course-create" aria-labelledby="create-user-title">
            <h2 id="create-user-title">{{ __('Añadir alumno') }}</h2>
            <p class="auth-description">{{ __('El alumno quedará dado de alta previamente y podrá activar su cuenta desde el registro.') }}</p>
            <form class="auth-form" method="POST" action="{{ route('admin.users.store') }}"><input type="hidden" name="_admin_tab" value="admin-users">
                @csrf
                <div class="form-grid">
                    <div class="form-field"><label for="user-name">{{ __('Nombre') }}</label><input id="user-name" name="name" value="{{ old('name') }}" maxlength="255" required></div>
                    <div class="form-field"><label for="user-email">{{ __('Correo electrónico') }}</label><input id="user-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required></div>
                    <div class="form-field"><label for="user-phone">{{ __('Teléfono') }}</label><input id="user-phone" name="phone" value="{{ old('phone') }}" maxlength="30"></div>
                    <div class="form-field"><label for="user-birth-date">{{ __('Fecha de nacimiento') }}</label><input id="user-birth-date" name="birth_date" type="date" value="{{ old('birth_date') }}"></div>
                    <div class="form-field"><label for="user-class">{{ __('Clase') }}</label><select id="user-class" name="school_class_id"><option value="">{{ __('Sin asignar') }}</option>@foreach($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id') === (string) $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select></div>
                    <div class="form-field"><label for="user-address">{{ __('Dirección') }}</label><input id="user-address" name="address" value="{{ old('address') }}" maxlength="255"></div>
                    <div class="form-field"><label for="user-notes">{{ __('Notas internas') }}</label><textarea id="user-notes" name="admin_notes" rows="2" maxlength="5000">{{ old('admin_notes') }}</textarea></div>
                    <label class="checkbox-field"><input type="checkbox" name="is_admin" value="1"> {{ __('Dar permisos de administrador') }}</label>
                    <label class="checkbox-field"><input type="checkbox" name="is_teacher" value="1"> {{ __('Dar rol de profesor') }}</label>
                </div>
                <button class="button" type="submit">{{ __('Añadir alumno') }}</button>
            </form>
        </section>
        <section class="auth-card course-create" aria-labelledby="students-title">
            <h2 id="students-title">{{ __('Gestión de alumnos') }}</h2>
            <p class="auth-description">{{ __('Crea, consulta, edita y elimina cuentas. Solo los administradores pueden acceder a estas acciones.') }}</p>
        </section>
        <div class="auth-card admin-users">
            <h2 id="users-title">{{ __('Usuarios registrados') }}</h2>
            <div class="table-scroll" role="region" aria-labelledby="users-title" tabindex="0">
                <table class="users-table">
                    <caption class="sr-only">{{ __('Cuentas de Eskolak, de más reciente a más antigua') }}</caption>
                    <thead><tr><th scope="col">{{ __('Alumno') }}</th><th scope="col">{{ __('Contacto') }}</th><th scope="col">{{ __('Información') }}</th><th scope="col">{{ __('Rol y acciones') }}</th></tr></thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><span class="table-subtext">{{ $user->is_registered ? __('Cuenta activa') : __('Pendiente de registro') }}</span></td>
                                <td>{{ $user->email }}<span class="table-subtext">{{ $user->phone ?: __('Sin teléfono') }}</span></td>
                                <td><span class="table-subtext">{{ __('Nacimiento:') }} {{ $user->birth_date?->format('d/m/Y') ?? __('No indicado') }}</span><span class="table-subtext">{{ __('Dirección:') }} {{ $user->address ?: __('No indicada') }}</span>@if($user->admin_notes)<span class="table-subtext">{{ __('Nota:') }} {{ Str::limit($user->admin_notes, 60) }}</span>@endif</td>
                                <td>
                                    <span class="role-badge {{ $user->is_admin ? 'role-admin' : ($user->is_teacher ? 'role-teacher' : '') }}">{{ $user->is_admin ? __('Administrador') : ($user->is_teacher ? __('Profesor') : __('Alumno')) }}</span>
                                    @if($user->is_admin && $user->is_teacher)<span class="role-badge role-teacher">{{ __('Profesor') }}</span>@endif
                                    <details>
                                        <summary>{{ __('Editar ficha') }}</summary>
                                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="user-edit-form">
                                            <input type="hidden" name="_admin_tab" value="admin-users">@csrf @method('PUT')
                                            <label for="edit-name-{{ $user->id }}">{{ __('Nombre') }}</label><input id="edit-name-{{ $user->id }}" name="name" value="{{ $user->name }}" required>
                                            <label for="edit-email-{{ $user->id }}">{{ __('Correo electrónico') }}</label><input id="edit-email-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" required>
                                            <label for="edit-phone-{{ $user->id }}">{{ __('Teléfono') }}</label><input id="edit-phone-{{ $user->id }}" name="phone" value="{{ $user->phone }}">
                                            <label for="edit-birth-date-{{ $user->id }}">{{ __('Fecha de nacimiento') }}</label><input id="edit-birth-date-{{ $user->id }}" name="birth_date" type="date" value="{{ $user->birth_date?->format('Y-m-d') }}">
                                            <label for="edit-class-{{ $user->id }}">{{ __('Clase') }}</label><select id="edit-class-{{ $user->id }}" name="school_class_id"><option value="">{{ __('Sin asignar') }}</option>@foreach($classes as $schoolClass)<option value="{{ $schoolClass->id }}" @selected($user->school_class_id === $schoolClass->id)>{{ $schoolClass->name }}</option>@endforeach</select>
                                            <label for="edit-address-{{ $user->id }}">{{ __('Dirección') }}</label><input id="edit-address-{{ $user->id }}" name="address" value="{{ $user->address }}">
                                            <label for="edit-notes-{{ $user->id }}">{{ __('Notas internas') }}</label><textarea id="edit-notes-{{ $user->id }}" name="admin_notes" rows="2">{{ $user->admin_notes }}</textarea>
                                            <label for="edit-password-{{ $user->id }}">{{ __('Nueva contraseña (opcional)') }}</label><input id="edit-password-{{ $user->id }}" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" placeholder="{{ __('Nueva contraseña (opcional)') }}">
                                            <label for="edit-password-confirmation-{{ $user->id }}">{{ __('Repite la nueva contraseña') }}</label><input id="edit-password-confirmation-{{ $user->id }}" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" placeholder="{{ __('Repite la nueva contraseña') }}">
                                            <label><input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)> {{ __('Administrador') }}</label>
                                            <label><input type="checkbox" name="is_teacher" value="1" @checked($user->is_teacher)> {{ __('Profesor') }}</label>
                                            <button class="button button-small" type="submit">{{ __('Guardar ficha') }}</button>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm-delete="{{ __('¿Seguro que quieres eliminar este registro?') }}"><input type="hidden" name="_admin_tab" value="admin-users">@csrf @method('DELETE')<button class="text-link danger-link" type="submit">{{ __('Eliminar') }}</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">{{ __('Todavía no hay usuarios registrados.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $users->appends(['tab' => 'admin-users'])->links() }}</div>
        </div>
        </div>
    </section>
@endsection
