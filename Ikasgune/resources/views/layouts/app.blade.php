<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f8f2">
    <link rel="icon" type="image/png" href="{{ asset('images/ikasgune-logo.png') }}">
    <meta name="description" content="{{ __('Eskolak, un espacio para aprender, compartir y crecer.') }}">
    <meta property="og:title" content="@yield('title', __('Eskolak · Tu espacio para aprender'))">
    <meta property="og:description" content="{{ __('Aprende, comparte y crece con Eskolak.') }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/ikasgune-logo.png') }}">
    <title>@yield('title', __('Eskolak · Tu espacio para aprender'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#contenido">{{ __('Saltar al contenido') }}</a>
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
            <a class="brand" href="{{ route('inicio') }}" aria-label="{{ __('Eskolak, inicio') }}"><img class="brand-logo" src="{{ asset('images/ikasgune-logo.png') }}" alt="" width="48" height="48"> eskolak<span class="brand-dot">.</span></a>
            <span class="header-caption">{{ __('Un lugar para ir más allá.') }}</span>
            <form class="language-switcher" method="POST" action="{{ route('locale.update') }}">
                @csrf
                <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                <label class="sr-only" for="locale">{{ __('Idioma') }}</label>
                <select id="locale" name="locale">
                    <option value="es" lang="es" @selected(app()->getLocale() === 'es')>Castellano</option>
                    <option value="eu" lang="eu" @selected(app()->getLocale() === 'eu')>Euskera</option>
                    <option value="en" lang="en" @selected(app()->getLocale() === 'en')>English</option>
                </select>
                <button type="submit" aria-label="{{ __('Cambiar idioma') }}">→</button>
            </form>
        </div>
    </header>
    <main id="contenido" tabindex="-1">@yield('content')</main>
    @if(session('status'))
        <div class="toast" role="status" data-toast>{{ session('status') }}<button type="button" aria-label="{{ __('Cerrar notificación') }}" data-dismiss-toast>×</button></div>
    @endif
    <div class="modal-backdrop" data-logout-modal hidden>
        <section class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="logout-modal-title" aria-describedby="logout-modal-description">
            <div class="confirm-modal-icon" aria-hidden="true"><svg><use href="#icon-logout"/></svg></div>
            <p class="eyebrow">{{ __('Tu espacio Eskolak') }}</p>
            <h2 id="logout-modal-title">{{ __('¿Quieres cerrar sesión?') }}</h2>
            <p id="logout-modal-description">{{ __('Tu progreso queda guardado. Podrás volver cuando quieras.') }}</p>
            <div class="confirm-modal-actions">
                <button class="button button-secondary" type="button" data-close-logout-modal>{{ __('Seguir aquí') }}</button>
                <button class="button" type="button" data-confirm-logout-action>{{ __('Cerrar sesión') }}</button>
            </div>
        </section>
    </div>
    <footer class="site-footer">
        <div class="container footer-inner">
            <div><a class="brand" href="{{ route('inicio') }}"><img class="brand-logo" src="{{ asset('images/ikasgune-logo.png') }}" alt="" width="48" height="48">eskolak.</a><p>{{ __('Un espacio para seguir aprendiendo.') }}</p></div>
            <p>© {{ date('Y') }} Eskolak</p>
            <a href="#contenido">{{ __('Volver arriba') }} <span aria-hidden="true">↑</span></a>
        </div>
    </footer>
    <nav class="bottom-nav" aria-label="{{ __('Navegación principal') }}">
        <a class="dock-item" data-tab="home" href="{{ route('inicio') }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>
            <svg aria-hidden="true"><use href="#icon-home"/></svg><span>{{ __('Inicio') }}</span>
        </a>
        <a class="dock-item" href="{{ route('courses.index') }}" @if(request()->routeIs('courses.*')) aria-current="page" @endif>
            <svg aria-hidden="true"><use href="#icon-explore"/></svg><span>{{ __('Cursos') }}</span>
        </a>
        @auth
            @can('access-admin')
                <a class="dock-item" href="{{ route('admin.index') }}" @if(request()->routeIs('admin.*')) aria-current="page" @endif>
                    <svg aria-hidden="true"><use href="#icon-admin"/></svg><span>{{ __('Admin') }}</span>
                </a>
            @endcan
            <a class="dock-item" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-person"/></svg><span>{{ __('Mi espacio') }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" data-confirm-logout>
                @csrf
                <button class="dock-item" type="submit"><svg aria-hidden="true"><use href="#icon-logout"/></svg><span>{{ __('Cerrar sesión') }}</span></button>
            </form>
        @else
            <a class="dock-item" href="{{ route('register') }}" @if(request()->routeIs('register')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-register"/></svg><span>{{ __('Crear cuenta') }}</span>
            </a>
            <a class="dock-item" href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>
                <svg aria-hidden="true"><use href="#icon-person"/></svg><span>{{ __('Iniciar sesión') }}</span>
            </a>
        @endauth
    </nav>
</body>
</html>
