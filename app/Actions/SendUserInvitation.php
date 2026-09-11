<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\URL;

/**
 * Verschickt die Einladungsmail und merkt sich den Zeitpunkt am Nutzer.
 */
class SendUserInvitation
{
    public function handle(User $user): void
    {
        $validDays = (int) config('skriptdepot.invitation_valid_days');

        $acceptUrl = URL::temporarySignedRoute(
            'filament.portal.invitation.accept',
            now()->addDays($validDays),
            ['user' => $user],
        );

        $user->forceFill(['invited_at' => now()])->save();

        $user->notify(new UserInvitation($acceptUrl, $validDays));
    }
}
