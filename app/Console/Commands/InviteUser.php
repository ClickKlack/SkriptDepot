<?php

namespace App\Console\Commands;

use App\Actions\SendUserInvitation;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Verschickt die Einladung eines Nutzers erneut, etwa wenn der erste Versand am Mailserver scheiterte.
 */
class InviteUser extends Command
{
    protected $signature = 'user:invite {email : E-Mail-Adresse des Nutzers}';

    protected $description = 'Verschickt die Einladungsmail an einen noch nicht aktivierten Nutzer (erneut)';

    public function handle(SendUserInvitation $sendInvitation): int
    {
        $email = (string) $this->argument('email');
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("Kein Konto mit der Adresse {$email}.");

            return self::FAILURE;
        }

        if ($user->hasVerifiedEmail()) {
            $this->warn("{$email} hat die Einladung bereits angenommen.");

            return self::INVALID;
        }

        if ($user->isBlocked()) {
            $this->error("{$email} ist gesperrt.");

            return self::FAILURE;
        }

        $sendInvitation->handle($user);
        $this->info("Einladung an {$email} verschickt.");

        return self::SUCCESS;
    }
}
