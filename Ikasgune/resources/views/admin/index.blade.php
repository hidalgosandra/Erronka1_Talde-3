@extends('layouts.app')

@section('title', 'Administración · Ikasgune')

@section('content')
    <section class="container dashboard-section" aria-labelledby="admin-title">
        <p class="eyebrow">IKASGUNE · ADMINISTRACIÓN</p>
        <h1 id="admin-title">Panel de administración</h1>
        <p class="hero-description">Gestiona cursos y alumnos desde un único espacio privado.</p>
        <div class="admin-tabs" role="tablist" aria-label="Secciones de administración">
            <button class="admin-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="admin-overview" data-admin-tab="admin-overview">Resumen</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" aria-controls="admin-courses" data-admin-tab="admin-courses">Cursos</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" aria-controls="admin-users" data-admin-tab="admin-users">Alumnos</button>
        </div>
        <div id="admin-overview" class="admin-panel is-active" role="tabpanel">
        <div class="admin-stats">
            <article class="auth-card"><h2>Usuarios</h2><p class="stat-value">{{ $totalUsers }}</p></article>
            <article class="auth-card"><h2>Administradores</h2><p class="stat-value">{{ $totalAdmins }}</p></article>
            <article class="auth-card"><h2>Cuentas normales</h2><p class="stat-value">{{ $totalUsers - $totalAdmins }}</p></article>
        </div>
        </div>
        <div id="admin-courses" class="admin-panel" role="tabpanel" hidden>
        <section id="crear-curso" class="auth-card course-create" aria-labelledby="create-course-title">
            <h2 id="create-course-title">Crear un curso</h2>
            <p class="auth-description">Aparecerá en el catálogo y los usuarios podrán inscribirse.</p>
            <form class="auth-form" method="POST" action="{{ route('admin.courses.store') }}">
                @csrf
                <div class="form-field">
                    <label for="title">Título</label>
                    <input id="title" name="title" value="{{ old('title') }}" maxlength="255" required @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
                    @error('title')<p id="title-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-field">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" rows="4" maxlength="10000" required @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                    @error('description')<p id="description-error" class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="form-grid">
                    <div class="form-field"><label for="category">Categoría</label><input id="category" name="category" value="{{ old('category', 'General') }}" maxlength="80" required></div>
                    <div class="form-field"><label for="level">Nivel</label><select id="level" name="level"><option>Todos los niveles</option><option>Inicial</option><option>Intermedio</option><option>Avanzado</option></select></div>
                    <div class="form-field"><label for="duration_minutes">Duración (minutos)</label><input id="duration_minutes" name="duration_minutes" type="number" min="15" max="1000" value="{{ old('duration_minutes', 60) }}" required></div>
                    <label class="checkbox-field"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))> Mostrar en destacados</label>
                </div>
                <div><button class="button" type="submit">Publicar curso</button> <a class="text-link" href="{{ route('courses.index') }}">Ver catálogo →</a></div>
            </form>
        </section>
        <section class="auth-card admin-users" aria-labelledby="courses-title">
            <h2 id="courses-title">Cursos publicados</h2>
            <div class="admin-record-grid">
                @forelse($courses as $course)
                    <article class="record-card">
                        <div><span class="preview-label">{{ $course->category }} · {{ $course->level }}</span><h3>{{ $course->title }}</h3><p>{{ Str::limit($course->description, 100) }}</p></div>
                        <details><summary>Editar curso</summary>
                            <form class="auth-form" method="POST" action="{{ route('admin.courses.update', $course) }}">
                                @csrf @method('PUT')
                                <div class="form-field"><label for="course-title-{{ $course->id }}">Izenburua</label><input id="course-title-{{ $course->id }}" name="title" value="{{ $course->title }}" required></div>
                                <div class="form-field"><label for="course-description-{{ $course->id }}">Deskribapena</label><textarea id="course-description-{{ $course->id }}" name="description" rows="3" required>{{ $course->description }}</textarea></div>
                                <div class="form-grid"><input name="category" value="{{ $course->category }}" aria-label="Kategoria" required><select name="level" aria-label="Maila"><option @selected($course->level === 'Todos los niveles')>Todos los niveles</option><option @selected($course->level === 'Inicial')>Inicial</option><option @selected($course->level === 'Intermedio')>Intermedio</option><option @selected($course->level === 'Avanzado')>Avanzado</option></select><input name="duration_minutes" type="number" min="15" value="{{ $course->duration_minutes }}" aria-label="Iraupena" required></div>
                                <label class="checkbox-field"><input type="checkbox" name="is_featured" value="1" @checked($course->is_featured)> Nabarmendua</label>
                                <button class="button button-small" type="submit">Guardar cambios</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" data-confirm-delete>@csrf @method('DELETE')<button class="text-link danger-link" type="submit">Eliminar</button></form>
                    </article>
                @empty
                    <p class="auth-description">Todavía no hay cursos publicados.</p>
                @endforelse
            </div>
        </section>
        </div>
        <div id="admin-users" class="admin-panel" role="tabpanel" hidden>
        <section class="auth-card course-create" aria-labelledby="create-user-title">
            <h2 id="create-user-title">Añadir alumno</h2>
            <p class="auth-description">El alumno quedará dado de alta previamente y podrá activar su cuenta desde el registro.</p>
            <form class="auth-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-field"><label for="user-name">Nombre</label><input id="user-name" name="name" value="{{ old('name') }}" maxlength="255" required></div>
                    <div class="form-field"><label for="user-email">Correo electrónico</label><input id="user-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required></div>
                    <div class="form-field"><label for="user-phone">Teléfono</label><input id="user-phone" name="phone" value="{{ old('phone') }}" maxlength="30"></div>
                    <div class="form-field"><label for="user-birth-date">Fecha de nacimiento</label><input id="user-birth-date" name="birth_date" type="date" value="{{ old('birth_date') }}"></div>
                    <div class="form-field"><label for="user-address">Dirección</label><input id="user-address" name="address" value="{{ old('address') }}" maxlength="255"></div>
                    <div class="form-field"><label for="user-notes">Notas internas</label><textarea id="user-notes" name="admin_notes" rows="2" maxlength="5000">{{ old('admin_notes') }}</textarea></div>
                    <label class="checkbox-field"><input type="checkbox" name="is_admin" value="1"> Dar permisos de administrador</label>
                </div>
                <button class="button" type="submit">Añadir alumno</button>
            </form>
        </section>
        <section class="auth-card course-create" aria-labelledby="students-title">
            <h2 id="students-title">Gestión de alumnos</h2>
            <p class="auth-description">Crea, consulta, edita y elimina cuentas. Solo los administradores pueden acceder a estas acciones.</p>
        </section>
        <div class="auth-card admin-users">
            <h2 id="users-title">Usuarios registrados</h2>
            <div class="table-scroll" role="region" aria-labelledby="users-title" tabindex="0">
                <table class="users-table">
                    <caption class="sr-only">Cuentas de Ikasgune, de más reciente a más antigua</caption>
                    <thead><tr><th scope="col">Alumno</th><th scope="col">Contacto</th><th scope="col">Información</th><th scope="col">Rol y acciones</th></tr></thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><span class="table-subtext">{{ $user->is_registered ? 'Cuenta activa' : 'Pendiente de registro' }}</span></td>
                                <td>{{ $user->email }}<span class="table-subtext">{{ $user->phone ?: 'Sin teléfono' }}</span></td>
                                <td><span class="table-subtext">Nacimiento: {{ $user->birth_date?->format('d/m/Y') ?? 'No indicado' }}</span><span class="table-subtext">Dirección: {{ $user->address ?: 'No indicada' }}</span>@if($user->admin_notes)<span class="table-subtext">Nota: {{ Str::limit($user->admin_notes, 60) }}</span>@endif</td>
                                <td><span class="role-badge {{ $user->is_admin ? 'role-admin' : '' }}">{{ $user->is_admin ? 'Administrador' : 'Alumno' }}</span><details><summary>Editar ficha</summary><form method="POST" action="{{ route('admin.users.update', $user) }}" class="user-edit-form">@csrf @method('PUT')<input name="name" value="{{ $user->name }}" aria-label="Nombre" required><input name="email" type="email" value="{{ $user->email }}" aria-label="Correo electrónico" required><input name="phone" value="{{ $user->phone }}" aria-label="Teléfono"><input name="birth_date" type="date" value="{{ $user->birth_date?->format('Y-m-d') }}" aria-label="Fecha de nacimiento"><input name="address" value="{{ $user->address }}" aria-label="Dirección"><textarea name="admin_notes" rows="2" aria-label="Notas internas">{{ $user->admin_notes }}</textarea><input name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" placeholder="Nueva contraseña (opcional)" aria-label="Nueva contraseña"><input name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" placeholder="Repite la nueva contraseña" aria-label="Repite la nueva contraseña"><label><input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)> Administrador</label><button class="button button-small" type="submit">Guardar ficha</button></form></details><form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm-delete>@csrf @method('DELETE')<button class="text-link danger-link" type="submit">Eliminar</button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Todavía no hay usuarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $users->links() }}</div>
        </div>
        </div>
    </section>
@endsection
