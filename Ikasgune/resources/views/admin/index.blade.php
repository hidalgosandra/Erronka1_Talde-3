@extends('layouts.app')

@section('title', 'Administración · Ikasgune')

@section('content')
    <section class="container dashboard-section" aria-labelledby="admin-title">
        <p class="eyebrow">IKASGUNE · ADMINISTRACIÓN</p>
        <h1 id="admin-title">Panel de administración</h1>
        <p class="hero-description">Consulta las cuentas registradas y sus permisos.</p>
        <div class="admin-stats">
            <article class="auth-card"><h2>Usuarios</h2><p class="stat-value">{{ $totalUsers }}</p></article>
            <article class="auth-card"><h2>Administradores</h2><p class="stat-value">{{ $totalAdmins }}</p></article>
            <article class="auth-card"><h2>Cuentas normales</h2><p class="stat-value">{{ $totalUsers - $totalAdmins }}</p></article>
        </div>
        <section id="crear-curso" class="auth-card course-create" aria-labelledby="create-course-title">
            <h2 id="create-course-title">Publicar un curso</h2>
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
                <div><button class="button" type="submit">Publicar curso</button> <a class="text-link" href="{{ route('courses.index') }}">Ver catálogo →</a></div>
            </form>
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
                                <td>{{ $user->name }}</td><td>{{ $user->email }}</td>
                                <td><span class="role-badge {{ $user->is_admin ? 'role-admin' : '' }}">{{ $user->is_admin ? 'Administrador' : 'Usuario' }}</span></td>
                                <td>{{ $user->created_at?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Todavía no hay usuarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $users->links() }}</div>
        </div>
    </section>
@endsection
