<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailDelivery
{
    public static function send(Closure $callback, string $field = 'email'): mixed
    {
        if (! Mail::isFake() && ! Notification::isFake()) {
            $mailer = config('mail.default');
            $smtp = config('mail.mailers.smtp');
            if (in_array($mailer, ['log', 'array'], true) || ($mailer === 'smtp' && $smtp['host'] === 'smtp.gmail.com' && (blank($smtp['username']) || blank($smtp['password'])))) {
                throw ValidationException::withMessages([$field => __('El envío de correo todavía no está configurado. Contacta con el administrador.')]);
            }
        }
        try {
            return $callback();
        } catch (TransportExceptionInterface $exception) {
            // Do not expose SMTP credentials or transport diagnostics in the response.
            throw ValidationException::withMessages([$field => __('No se ha podido enviar el correo. Inténtalo de nuevo más tarde.')]);
        }
    }
}
