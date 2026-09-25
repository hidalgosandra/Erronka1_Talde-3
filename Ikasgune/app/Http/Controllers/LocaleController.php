<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate(['locale' => ['required', 'string', 'in:es,eu,en']]);
        $request->session()->put('locale', $data['locale']);

        $returnTo = $request->input('return_to', '/');
        if (! is_string($returnTo) || ! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//') || str_contains($returnTo, '\\') || preg_match('/[\x00-\x20]/', $returnTo)) {
            $returnTo = '/';
        }

        return redirect($returnTo);
    }
}
