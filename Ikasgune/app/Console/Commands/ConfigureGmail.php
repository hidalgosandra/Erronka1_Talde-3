<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

#[Signature('eskolak:configure-gmail')]
#[Description('Configura una cuenta real de Gmail y comprueba el acceso SMTP sin enviar correos')]
class ConfigureGmail extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Ejecuta php artisan eskolak:configure-gmail en tu terminal, sin --no-interaction.');

            return self::FAILURE;
        }

        $path = app()->environmentFilePath();
        if (! is_file($path) || ! is_writable($path)) {
            $this->error('No se puede escribir en el archivo .env. Comprueba que exista y tengas permisos.');

            return self::FAILURE;
        }

        $original = File::get($path);
        $this->info('Activa la verificación en dos pasos y crea una contraseña de aplicación en https://myaccount.google.com/apppasswords');
        $email = strtolower(trim((string) $this->ask('Correo real de Gmail para enviar los códigos')));
        $password = str_replace(' ', '', trim((string) $this->secret('Contraseña de aplicación de Google (16 caracteres, entrada oculta)', false)));
        $validator = Validator::make(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email', 'max:254', 'regex:/\A[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\z/', 'not_in:eskolak@talde3.com'],
            'password' => ['required', 'regex:/\A[a-zA-Z0-9]{16}\z/'],
        ], [
            'email.*' => 'Introduce una cuenta real de Gmail o Google Workspace; el remitente anterior era ficticio.',
            'password.*' => 'Introduce la contraseña de aplicación de 16 caracteres que genera Google, no tu contraseña habitual.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $transport = Mail::build([
            'transport' => 'smtp',
            'scheme' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => $email,
            'password' => $password,
            'require_tls' => true,
            'timeout' => 15,
        ])->getSymfonyTransport();

        if (! $transport instanceof EsmtpTransport) {
            $this->error('No se ha podido preparar la conexión SMTP de Gmail.');

            return self::FAILURE;
        }

        $this->info('Comprobando la conexión segura y la autenticación con Gmail...');
        try {
            $transport->start();
        } catch (TransportExceptionInterface) {
            $this->error('No se ha podido autenticar con Gmail. Revisa la cuenta, su contraseña de aplicación y la conexión al puerto 587. No se ha modificado .env.');

            return self::FAILURE;
        } finally {
            $transport->stop();
        }

        if (File::get($path) !== $original) {
            $this->error('El archivo .env ha cambiado durante la comprobación. Ejecuta el comando de nuevo.');

            return self::FAILURE;
        }

        $settings = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_URL' => 'null',
            'MAIL_SCHEME' => 'smtp',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'MAIL_REQUIRE_TLS' => 'true',
            'MAIL_USERNAME' => $email,
            'MAIL_PASSWORD' => $password,
            'MAIL_FROM_ADDRESS' => $email,
            'MAIL_FROM_NAME' => '"Eskolak"',
            'MAIL_TIMEOUT' => '15',
        ];
        $contents = $original;
        foreach ($settings as $key => $value) {
            $contents = preg_replace('/^[\t ]*(?:export[\t ]+)?'.preg_quote($key, '/').'[\t ]*=.*(?:\r?\n|$)/m', '', $contents);
        }
        $contents = rtrim($contents)."\n\n";
        foreach ($settings as $key => $value) {
            $contents .= $key.'='.$value."\n";
        }

        File::replace($path, $contents, 0600);
        if ($this->callSilent('config:clear') !== self::SUCCESS) {
            $this->error('La configuración está guardada, pero debes ejecutar php artisan config:clear antes de probar el registro.');

            return self::FAILURE;
        }
        $this->info('Gmail autenticado y configuración guardada. Reinicia composer run dev y prueba el registro. No se ha enviado ningún correo de prueba.');

        return self::SUCCESS;
    }
}
