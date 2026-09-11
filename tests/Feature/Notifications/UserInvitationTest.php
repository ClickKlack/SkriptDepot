<?php

use App\Models\User;
use App\Notifications\UserInvitation;

it('baut die Einladungsmail in der jeweiligen Sprache', function (string $locale, string $subject) {
    app()->setLocale($locale);
    $user = User::factory()->make(['name' => 'Erika', 'locale' => $locale]);

    $mail = (new UserInvitation('https://portal.example.test/portal/invitation/1?signature=x', 7))->toMail($user);

    expect($mail->subject)->toBe($subject)
        ->and($mail->actionUrl)->toBe('https://portal.example.test/portal/invitation/1?signature=x')
        ->and($mail->greeting)->toContain('Erika');
})->with([
    'deutsch' => ['de', 'Deine Einladung zu SkriptDepot'],
    'englisch' => ['en', 'Your invitation to SkriptDepot'],
]);

it('rendert den Mail-Rahmen vollständig auf Deutsch', function () {
    app()->setLocale('de');
    $user = User::factory()->make(['name' => 'Erika', 'locale' => 'de']);

    $html = (string) (new UserInvitation('https://portal.example.test/portal/invitation/1?signature=x', 7))->toMail($user)->render();

    expect($html)
        ->toContain('Viele Grüße,')
        ->toContain('Alle Rechte vorbehalten.')
        ->toContain('nicht anklicken lässt')
        ->not->toContain('Regards')
        ->not->toContain('All rights reserved')
        ->not->toContain('having trouble');
});
