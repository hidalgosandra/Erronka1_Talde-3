<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', fn (User $user): bool => $user->is_admin === true);
        Gate::define('access-teacher', fn (User $user): bool => $user->is_teacher === true);
        Gate::define('manage-course', fn (User $user, Course $course): bool => $user->is_teacher === true && $course->teacher_id === $user->id);
        ResetPassword::toMailUsing(function (User $user, string $token): MailMessage {
            $url = rtrim(config('app.url'), '/').route('password.reset', ['token' => $token, 'email' => $user->email], false);

            return (new MailMessage)->subject('Recupera tu contraseña de Eskolak')
                ->greeting('Hola, '.$user->name)
                ->line('Hemos recibido una solicitud para restablecer tu contraseña.')
                ->action('Elegir una nueva contraseña', $url)
                ->line('Este enlace caduca en '.config('auth.passwords.users.expire').' minutos.')
                ->line('Si no has solicitado este cambio, puedes ignorar este mensaje.');
        });
    }
}
