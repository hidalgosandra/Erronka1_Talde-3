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
                    <thead><tr><th scope="col">Nombre</th><th scope="col">Correo</th><th scope="col">Rol</th><th scope="col">Fecha de alta</th></tr></thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><details><summary>Editar</summary><form method="POST" action="{{ route('admin.users.update', $user) }}" class="inline-edit">@csrf @method('PUT')<input name="name" value="{{ $user->name }}" required><input name="email" type="email" value="{{ $user->email }}" required><label><input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)> Administrador</label><button class="button button-small" type="submit">Guardar</button></form></details></td><td>{{ $user->email }}</td>
                                <td><span class="role-badge {{ $user->is_admin ? 'role-admin' : '' }}">{{ $user->is_admin ? 'Administrador' : 'Usuario' }}</span></td>
                                <td>{{ $user->created_at?->format('d/m/Y') ?? '—' }}<form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm-delete>@csrf @method('DELETE')<button class="text-link danger-link" type="submit">Eliminar</button></form></td>
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
