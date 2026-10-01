<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f7f8f2" data-theme-color>
    <link rel="icon" type="image/png" sizes="96x96" href="https://www.google.com/chrome/static/images/favicons/favicon-96x96.png">
    <meta name="description" content="{{ __('Eskolak, un espacio para aprender, compartir y crecer.') }}">
    <meta property="og:title" content="@yield('title', __('Eskolak · Tu espacio para aprender'))">
    <meta property="og:description" content="{{ __('Aprende, comparte y crece con Eskolak.') }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/ikasgune-logo.png') }}">
    <title>@yield('title', __('Eskolak · Tu espacio para aprender'))</title>
    <script>
        {!! file_get_contents(resource_path('js/theme.js')) !!}
    </script>
    @php
        $pageVite = clone app(\Illuminate\Foundation\Vite::class);
        // A public tunnel cannot reach the developer's localhost Vite server.
        if (str_ends_with(request()->getHost(), '.devtunnels.ms')) {
            $pageVite->useHotFile(storage_path('framework/vite-tunnel-disabled.hot'));
        }
    @endphp
    {{ $pageVite(['resources/css/app.css', 'resources/js/app.js']) }}
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
        <symbol id="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></symbol>
        <symbol id="icon-moon" viewBox="0 0 24 24"><path d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z"/></symbol>
    </svg>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('inicio') }}" aria-label="{{ __('Eskolak, inicio') }}"><img class="brand-logo" src="{{ asset('images/ikasgune-logo.png') }}" alt="" width="48" height="48"> eskolak<span class="brand-dot">.</span></a>
            <span class="header-caption">{{ __('Un lugar para ir más allá.') }}</span>
            <div class="header-controls island-anchor">
                <details class="preferences-island" data-preferences-island>
                    <summary class="island-trigger" aria-label="{{ __('Cambiar idioma') }} · {{ __('Cambiar tema') }}">
                        <span class="island-status" aria-hidden="true"></span>
                        <span class="island-locale">{{ strtoupper(app()->getLocale()) }}</span>
                        <span class="island-divider" aria-hidden="true"></span>
                        <svg class="island-sun" aria-hidden="true"><use href="#icon-sun"/></svg>
                        <svg class="island-moon" aria-hidden="true"><use href="#icon-moon"/></svg>
                        <svg class="island-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg>
                    </summary>
                    <div class="island-panel">
                        <p class="island-label">{{ __('Idioma') }}</p>
                <form class="language-switcher" method="POST" action="{{ route('locale.update') }}" aria-label="{{ __('Cambiar idioma') }}">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                    <span class="language-switcher-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/></svg>
                    </span>
                    <fieldset class="language-options">
                        <legend class="sr-only">{{ __('Idioma') }}</legend>
                        @foreach(['es' => 'Castellano', 'eu' => 'Euskera', 'en' => 'English'] as $locale => $label)
                            <button class="language-option" type="submit" name="locale" value="{{ $locale }}" lang="{{ $locale }}" aria-label="{{ $label }}" aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}" title="{{ $label }}">
                                <span class="language-option-name">{{ $label }}</span>
                                <span class="language-option-code" aria-hidden="true">{{ strtoupper($locale) }}</span>
                                <span class="language-option-dot" aria-hidden="true"></span>
                            </button>
                        @endforeach
                    </fieldset>
                </form>
                <p class="island-label">{{ __('Cambiar tema') }}</p>
                <fieldset class="theme-toggle header-theme-toggle" data-theme-toggle>
                    <legend class="sr-only">{{ __('Cambiar tema') }}</legend>
                    <button class="theme-option" type="button" data-set-theme="light" aria-label="{{ __('Tema claro') }}" aria-pressed="false" title="{{ __('Tema claro') }}">
                        <svg aria-hidden="true"><use href="#icon-sun"/></svg><span>{{ __('Claro') }}</span>
                    </button>
                    <button class="theme-option" type="button" data-set-theme="dark" aria-label="{{ __('Tema oscuro') }}" aria-pressed="false" title="{{ __('Tema oscuro') }}">
                        <svg aria-hidden="true"><use href="#icon-moon"/></svg><span>{{ __('Oscuro') }}</span>
                    </button>
                </fieldset>
                    </div>
                </details>
            </div>
        </div>
    </header>
    <main id="contenido" tabindex="-1">@yield('content')</main>
    @if(session('status'))
        <output class="toast" data-toast>{{ session('status') }}<button type="button" aria-label="{{ __('Cerrar notificación') }}" data-dismiss-toast>×</button></output>
    @endif
    <div class="modal-backdrop" data-logout-modal hidden>
        <dialog class="confirm-modal" open aria-modal="true" aria-labelledby="logout-modal-title" aria-describedby="logout-modal-description">
            <div class="confirm-modal-icon" aria-hidden="true"><svg><use href="#icon-logout"/></svg></div>
            <p class="eyebrow">{{ __('Tu espacio Eskolak') }}</p>
            <h2 id="logout-modal-title">{{ __('¿Quieres cerrar sesión?') }}</h2>
            <p id="logout-modal-description">{{ __('Tu progreso queda guardado. Podrás volver cuando quieras.') }}</p>
            <div class="confirm-modal-actions">
                <button class="button button-secondary" type="button" data-close-logout-modal>{{ __('Seguir aquí') }}</button>
                <button class="button" type="button" data-confirm-logout-action>{{ __('Cerrar sesión') }}</button>
            </div>
        </dialog>
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
            @can('access-teacher')
                <a class="dock-item" href="{{ route('teacher.index') }}" @if(request()->routeIs('teacher.*')) aria-current="page" @endif>
                    <svg aria-hidden="true"><use href="#icon-person"/></svg><span>{{ __('Docencia') }}</span>
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
