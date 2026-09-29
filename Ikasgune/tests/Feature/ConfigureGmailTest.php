<?php

namespace Tests\Feature;

use Dotenv\Dotenv;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\TestCase;

class ConfigureGmailTest extends TestCase
{
    private ?string $environmentPath = null;

    protected function tearDown(): void
    {
        if ($this->environmentPath !== null && is_file($this->environmentPath)) {
            unlink($this->environmentPath);
        }

        parent::tearDown();
    }

    public function test_authenticated_setup_replaces_mail_settings_without_changing_other_configuration(): void
    {
        $this->prepareEnvironment("APP_KEY=keep-this-key\nDB_DATABASE=keep-this-database\nMAIL_USERNAME=old@example.test\nMAIL_PASSWORD=old-password\nMAIL_USERNAME=duplicate@example.test\nMAIL_URL=smtp://old.example.test\n");
        $transport = Mockery::mock(EsmtpTransport::class);
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('stop')->once();
        $this->mockSmtp($transport);

        $this->artisan('eskolak:configure-gmail')
            ->expectsQuestion('Correo real de Gmail para enviar los códigos', ' ESKOLAK.TEST@gmail.com ')
            ->expectsQuestion('Contraseña de aplicación de Google (16 caracteres, entrada oculta)', 'abcd efgh ijkl mnop')
            ->expectsOutput('Gmail autenticado y configuración guardada. Reinicia composer run dev y prueba el registro. No se ha enviado ningún correo de prueba.')
            ->assertSuccessful();

        $contents = file_get_contents($this->environmentPath);
        $env = Dotenv::parse($contents);
        $this->assertSame('keep-this-key', $env['APP_KEY']);
        $this->assertSame('keep-this-database', $env['DB_DATABASE']);
        $this->assertSame('eskolak.test@gmail.com', $env['MAIL_USERNAME']);
        $this->assertSame('eskolak.test@gmail.com', $env['MAIL_FROM_ADDRESS']);
        $this->assertSame('abcdefghijklmnop', $env['MAIL_PASSWORD']);
        $this->assertSame('true', $env['MAIL_REQUIRE_TLS']);
        $this->assertSame('smtp.gmail.com', $env['MAIL_HOST']);
        $this->assertSame('587', $env['MAIL_PORT']);
        $this->assertSame('null', $env['MAIL_URL']);
        $this->assertSame(1, substr_count($contents, 'MAIL_USERNAME='));
        $this->assertStringNotContainsString('abcdefghijklmnop', Artisan::output());
        $this->assertStringNotContainsString('abcd efgh ijkl mnop', Artisan::output());
    }

    public function test_failed_smtp_authentication_keeps_existing_environment_and_hides_diagnostics(): void
    {
        $this->prepareEnvironment("APP_KEY=unchanged\nMAIL_PASSWORD=old-password\n");
        $original = file_get_contents($this->environmentPath);
        $transport = Mockery::mock(EsmtpTransport::class);
        $transport->shouldReceive('start')->once()->andThrow(new TransportException('private SMTP diagnostics and password'));
        $transport->shouldReceive('stop')->once();
        $this->mockSmtp($transport);

        $this->artisan('eskolak:configure-gmail')
            ->expectsQuestion('Correo real de Gmail para enviar los códigos', 'eskolak.test@gmail.com')
            ->expectsQuestion('Contraseña de aplicación de Google (16 caracteres, entrada oculta)', 'abcdefghijklmnop')
            ->expectsOutput('No se ha podido autenticar con Gmail. Revisa la cuenta, su contraseña de aplicación y la conexión al puerto 587. No se ha modificado .env.')
            ->assertFailed();

        $this->assertSame($original, file_get_contents($this->environmentPath));
        $this->assertStringNotContainsString('private SMTP diagnostics', Artisan::output());
        $this->assertStringNotContainsString('abcdefghijklmnop', Artisan::output());
    }

    #[TestWith(['eskolak@talde3.com', 'abcdefghijklmnop', 'Introduce una cuenta real de Gmail o Google Workspace; el remitente anterior era ficticio.'])]
    #[TestWith(['not-an-email', 'abcdefghijklmnop', 'Introduce una cuenta real de Gmail o Google Workspace; el remitente anterior era ficticio.'])]
    #[TestWith(['eskolak.test@gmail.com', 'normal-password', 'Introduce la contraseña de aplicación de 16 caracteres que genera Google, no tu contraseña habitual.'])]
    public function test_invalid_credentials_are_rejected_before_connecting_or_writing(string $email, string $password, string $error): void
    {
        $this->prepareEnvironment("APP_KEY=unchanged\n");
        Mail::shouldReceive('build')->never();

        $this->artisan('eskolak:configure-gmail')
            ->expectsQuestion('Correo real de Gmail para enviar los códigos', $email)
            ->expectsQuestion('Contraseña de aplicación de Google (16 caracteres, entrada oculta)', $password)
            ->expectsOutput($error)
            ->assertFailed();

        $this->assertSame("APP_KEY=unchanged\n", file_get_contents($this->environmentPath));
    }

    public function test_setup_does_not_overwrite_an_environment_edited_while_connecting(): void
    {
        $this->prepareEnvironment("APP_KEY=original\n");
        $transport = Mockery::mock(EsmtpTransport::class);
        $transport->shouldReceive('start')->once()->andReturnUsing(function (): void {
            file_put_contents($this->environmentPath, "APP_KEY=edited-by-user\n");
        });
        $transport->shouldReceive('stop')->once();
        $this->mockSmtp($transport);

        $this->artisan('eskolak:configure-gmail')
            ->expectsQuestion('Correo real de Gmail para enviar los códigos', 'eskolak.test@gmail.com')
            ->expectsQuestion('Contraseña de aplicación de Google (16 caracteres, entrada oculta)', 'abcdefghijklmnop')
            ->expectsOutput('El archivo .env ha cambiado durante la comprobación. Ejecuta el comando de nuevo.')
            ->assertFailed();

        $this->assertSame("APP_KEY=edited-by-user\n", file_get_contents($this->environmentPath));
    }

    public function test_noninteractive_setup_cannot_prompt_or_change_configuration(): void
    {
        $this->prepareEnvironment("APP_KEY=unchanged\n");
        Mail::shouldReceive('build')->never();

        $this->artisan('eskolak:configure-gmail', ['--no-interaction' => true])
            ->expectsOutput('Ejecuta php artisan eskolak:configure-gmail en tu terminal, sin --no-interaction.')
            ->assertFailed();

        $this->assertSame("APP_KEY=unchanged\n", file_get_contents($this->environmentPath));
    }

    private function prepareEnvironment(string $contents): void
    {
        $this->environmentPath = tempnam(sys_get_temp_dir(), 'eskolak-mail-test-');
        file_put_contents($this->environmentPath, $contents);
        $this->app->useEnvironmentPath(dirname($this->environmentPath));
        $this->app->loadEnvironmentFrom(basename($this->environmentPath));
    }

    private function mockSmtp(EsmtpTransport $transport): void
    {
        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('getSymfonyTransport')->once()->andReturn($transport);
        Mail::shouldReceive('build')->once()->with(Mockery::on(fn (array $config): bool => $config['host'] === 'smtp.gmail.com'
            && $config['port'] === 587
            && $config['require_tls'] === true
            && $config['username'] === 'eskolak.test@gmail.com'
            && $config['password'] === 'abcdefghijklmnop'
        ))->andReturn($mailer);
    }
}
