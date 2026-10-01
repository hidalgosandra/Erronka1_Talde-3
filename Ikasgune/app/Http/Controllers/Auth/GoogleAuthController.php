<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        if (blank($clientId) || blank(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('status', __('El acceso con Google todavía no está configurado.'));
        }

        $state = Str::random(64);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => config('services.google.redirect', route('login.google.callback')),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_oauth_state');
        $receivedState = (string) $request->query('state', '');
        abort_unless(is_string($expectedState) && $receivedState !== '' && hash_equals($expectedState, $receivedState), 419);

        $errorMessage = $request->filled('error') ? __('No se completó el acceso con Google.') : null;
        $profile = null;

        if ($errorMessage === null) {
            $request->validate(['code' => ['required', 'string', 'max:2048']]);

            try {
                $token = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'code' => $request->string('code')->toString(),
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => config('services.google.redirect', route('login.google.callback')),
                ])->throw()->json();

                $accessToken = data_get($token, 'access_token');
                abort_unless(is_string($accessToken) && $accessToken !== '', 401);

                $profile = Http::withToken($accessToken)
                    ->get('https://openidconnect.googleapis.com/v1/userinfo')
                    ->throw()
                    ->json();
            } catch (Throwable) {
                $errorMessage = __('No se pudo verificar la cuenta de Google. Inténtalo de nuevo.');
            }
        }

        if ($errorMessage !== null) {
            return redirect()->route('login')->with('status', $errorMessage);
        }

        $email = strtolower(trim((string) data_get($profile, 'email', '')));
        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL) && data_get($profile, 'email_verified') === true, 403);

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $user = new User;
            $user->email = $email;
            $user->password = Str::random(48);
            $user->name = trim((string) data_get($profile, 'name', '')) ?: Str::before($email, '@');
        }

        $user->is_registered = true;
        $user->email_verified_at ??= now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        $destination = match (true) {
            $user->is_admin => 'admin.index',
            $user->is_teacher => 'teacher.index',
            default => 'dashboard',
        };

        return redirect()->route($destination);
    }
}
