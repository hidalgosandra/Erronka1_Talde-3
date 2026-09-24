<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_creates_a_user_with_a_hashed_password(): void
    {
        $this->artisan('ikasgune:create-user')
            ->expectsQuestion('Nombre', 'Ander')
            ->expectsQuestion('Correo electrónico', 'ander@example.test')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'a-test-password-123')
            ->expectsQuestion('Repite la contraseña', 'a-test-password-123')
            ->expectsOutput('Usuario creado. Ya puedes iniciar sesión.')
            ->assertSuccessful();

        $user = User::where('email', 'ander@example.test')->sole();
        $this->assertSame('Ander', $user->name);
        $this->assertTrue(Hash::check('a-test-password-123', $user->password));
    }

    public function test_command_rejects_duplicate_emails_without_overwriting_the_user(): void
    {
        $user = User::factory()->create(['email' => 'ander@example.test']);

        $this->artisan('ikasgune:create-user')
            ->expectsQuestion('Nombre', 'Another user')
            ->expectsQuestion('Correo electrónico', $user->email)
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'a-test-password-123')
            ->expectsQuestion('Repite la contraseña', 'a-test-password-123')
            ->expectsOutput('Ya existe un usuario con ese correo.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($user->password, $user->fresh()->password);
    }

    public function test_command_rejects_short_and_mismatched_passwords(): void
    {
        $this->artisan('ikasgune:create-user')
            ->expectsQuestion('Nombre', 'Ander')
            ->expectsQuestion('Correo electrónico', 'ander@example.test')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'short')
            ->expectsQuestion('Repite la contraseña', 'different')
            ->expectsOutput('La contraseña debe tener al menos 12 caracteres.')
            ->expectsOutput('Las contraseñas no coinciden.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }
}
