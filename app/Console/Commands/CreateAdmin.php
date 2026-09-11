<?php

namespace App\Console\Commands;

use App\Actions\SendUserInvitation;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Legt den ersten Administrator an, etwa nach dem Deployment, wenn es noch kein Admin-Konto gibt.
 *
 * Ohne --password geht eine Einladungsmail raus; mit --password ist das Konto sofort nutzbar.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {name : Anzeigename} {email : E-Mail-Adresse} {--password= : Passwort direkt setzen statt Einladung zu verschicken} {--locale=de : Oberflächensprache}';

    protected $description = 'Legt einen Administrator an und verschickt die Einladung';

    public function handle(SendUserInvitation $sendInvitation): int
    {
        $email = (string) $this->argument('email');

        if (User::where('email', $email)->exists()) {
            $this->error("Es gibt bereits ein Konto mit der Adresse {$email}.");

            return self::FAILURE;
        }

        $password = $this->option('password');

        $user = User::create([
            'name' => (string) $this->argument('name'),
            'email' => $email,
            'locale' => (string) $this->option('locale'),
            'is_admin' => true,
            ...(is_string($password) && $password !== '' ? ['password' => $password] : []),
        ]);

        if (is_string($password) && $password !== '') {
            $user->forceFill(['email_verified_at' => now()])->save();
            $this->info("Administrator {$email} angelegt, Anmeldung mit dem angegebenen Passwort möglich.");

            return self::SUCCESS;
        }

        $sendInvitation->handle($user);
        $this->info("Administrator {$email} angelegt, Einladung verschickt.");

        return self::SUCCESS;
    }
}
