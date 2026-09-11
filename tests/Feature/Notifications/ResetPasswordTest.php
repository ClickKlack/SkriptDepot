<?php

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;

it('rendert die Reset-Mail vollständig auf Deutsch', function () {
    app()->setLocale('de');
    $user = User::factory()->make(['locale' => 'de']);
    $notification = new ResetPassword('token');
    $notification->url = 'https://portal.example.test/portal/password-reset/reset?token=x';

    $mail = $notification->toMail($user);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Passwort zurücksetzen')
        ->and($html)->toContain('Zurücksetzen des Passworts angefordert')
        ->toContain('Minuten gültig')
        ->not->toContain('You are receiving')
        ->not->toContain('Regards');
});
