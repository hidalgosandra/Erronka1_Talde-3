<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f8f2">
    <link rel="icon" type="image/png" href="{{ asset('images/ikasgune-logo.png') }}">
    <meta name="description" content="Ikasgune, un espacio para aprender, compartir y crecer.">
    <title>@yield('title', 'Ikasgune · Tu espacio para aprender')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <svg class="icon-library" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <symbol id="icon-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></symbol>
        <symbol id="icon-explore" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m16 8-2.5 5.5L8 16l2.5-5.5Z"/></symbol>
        <symbol id="icon-person" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></symbol>
        <symbol id="icon-register" viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 11-5.7M18 13v8m-4-4h8"/></symbol>
        <symbol id="icon-admin" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></symbol>
        <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M10 3H4v18h6m5-14 5 5-5 5M8 12h12"/></symbol>
    </svg>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('inicio') }}" aria-label="Ikasgune, inicio"><img class="brand-logo" src="{{ asset('images/ikasgune-logo.png') }}" alt="" width="48" height="48"> ikasgune<span class="brand-dot">.</span></a>
            <span class="header-caption">Un lugar para ir más allá.</span>
        </div>
    </header>
    <main id="contenido" tabindex="-1">@yield('content')</main>
    <footer class="site-footer">
        <div class="container footer-inner">
            <div><a class="brand" href="{{ route('inicio') }}"><img class="brand-logo" src="{{ asset('images/ikasgune-logo.png') }}" alt="" width="48" height="48">ikasgune.</a><p>Un espacio para seguir aprendiendo.</p></div>
            <p>© {{ date('Y') }} Ikasgune</p>
            <a href="#contenido">Volver arriba <span aria-hidden="true">↑</span></a>
        </div>
    </footer>
    <nav class="bottom-nav" aria-label="Navegación principal">
        <a class="dock-item" data-tab="home" href="{{ route('inicio') }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>
            <svg aria-hidden="true"><use href="#icon-home"/></svg><span>Inicio</span>
        </a>
        <a class="dock-item" href="{{ route('courses.index') }}" @if(request()->routeIs('courses.*')) aria-current="page" @endif>
            <svg aria-hidden="true"><use href="#icon-explore"/></svg><span>Cursos</span>
        </a>
        @auth
            @can('access-admin')
                <a class="dock-item" href="{{ route('admin.index') }}" @if(request()->routeIs('admin.*')) aria-current="page" @endif>
                    <svg aria-hidden="true"><use href="#icon-admin"/></svg><span>Admin</span>
                </a>
            @endcan
            <a class="dock-item" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-person"/></svg><span>Mi espacio</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dock-item" type="submit"><svg aria-hidden="true"><use href="#icon-logout"/></svg><span>Cerrar sesión</span></button>
            </form>
        @else
            <a class="dock-item" href="{{ route('register') }}" @if(request()->routeIs('register')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-register"/></svg><span>Crear cuenta</span>
            </a>
            <a class="dock-item" href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-person"/></svg><span>Iniciar sesión</span>
            </a>
        @endauth
    </nav>
</body>
</html>
