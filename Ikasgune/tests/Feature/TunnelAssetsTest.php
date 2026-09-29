<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class TunnelAssetsTest extends TestCase
{
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
