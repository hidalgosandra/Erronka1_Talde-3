<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ikasgune:make-admin {email : Correo de una cuenta existente}')]
#[Description('Concede acceso al panel de administración a una cuenta existente')]
class MakeAdmin extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No existe una cuenta con ese correo. Regístrala primero.');

            return self::FAILURE;
        }

        $user->is_admin = true;
        $user->save();
        $this->info('La cuenta ya tiene acceso a administración.');

        return self::SUCCESS;
    }
}
