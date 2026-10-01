<?php

namespace Tests\Feature;

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class TunnelAssetsTest extends TestCase
{
    public function test_tunnel_uses_built_assets_even_when_vite_is_running_locally(): void
    {
        $vite = new class extends Vite
        {
            public function isRunningHot(): bool
            {
                return ! str_ends_with($this->hotFile(), 'vite-tunnel-disabled.hot');
            }

            protected function hotAsset($asset): string
            {
                return 'http://localhost:5173/'.$asset;
            }

            protected function manifest($buildDirectory): array
            {
                return [
                    'resources/css/app.css' => ['file' => 'assets/app.css', 'src' => 'resources/css/app.css', 'isEntry' => true],
                    'resources/js/app.js' => ['file' => 'assets/app.js', 'src' => 'resources/js/app.js', 'isEntry' => true],
                ];
            }
        };
        $this->app->instance(Vite::class, $vite);
        Route::get('/theme-assets', fn () => view('layouts.app'));

        $this->get('https://eskolak-8000.devtunnels.ms/theme-assets')->assertOk()
            ->assertSee('/build/assets/app.css', false)->assertSee('/build/assets/app.js', false)
            ->assertDontSee('localhost:5173', false);
        $this->get('http://localhost:8000/theme-assets')->assertOk()->assertSee('http://localhost:5173/', false);
    }

    #[TestWith(['127.0.0.1'])]
    #[TestWith(['::1'])]
    public function test_local_proxy_generates_assets_and_links_using_public_https_address(string $proxy): void
    {
        Route::get('/tunnel-check', fn () => [
            'css' => asset('build/assets/app.css'),
            'login' => route('login'),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => $proxy])
            ->withHeaders(['X-Forwarded-Host' => 'eskolak.example.test', 'X-Forwarded-Proto' => 'https'])
            ->get('http://localhost:8000/tunnel-check')
            ->assertOk()->assertExactJson([
                'css' => 'https://eskolak.example.test/build/assets/app.css',
                'login' => 'https://eskolak.example.test/login',
            ]);
    }

    public function test_untrusted_client_cannot_override_public_host_with_forwarded_headers(): void
    {
        Route::get('/tunnel-check', fn () => ['css' => asset('build/assets/app.css')]);

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->withHeaders(['X-Forwarded-Host' => 'attacker.example.test', 'X-Forwarded-Proto' => 'https'])
            ->get('http://localhost:8000/tunnel-check')
            ->assertOk()->assertExactJson(['css' => 'http://localhost:8000/build/assets/app.css']);
    }
}
