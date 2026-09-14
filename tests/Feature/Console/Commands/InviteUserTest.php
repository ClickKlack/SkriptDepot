<?php

use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Notification;

it('verschickt die Einladung an einen noch nicht aktivierten Nutzer erneut', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->artisan('user:invite', ['email' => $user->email])->assertSuccessful();

    Notification::assertSentTo($user, UserInvitation::class);
    expect($user->fresh()->invited_at)->not->toBeNull();
});

it('verschickt nichts an aktivierte, gesperrte oder unbekannte Adressen', function (Closure $emailFor, int $exitCode) {
    Notification::fake();

    $this->artisan('user:invite', ['email' => $emailFor()])->assertExitCode($exitCode);

    Notification::assertNothingSent();
})->with([
    'bereits aktiviert' => [fn () => User::factory()->create()->email, 2],
    'gesperrt' => [fn () => User::factory()->unverified()->create(['blocked_at' => now()])->email, 1],
    'unbekannt' => [fn () => 'niemand@example.test', 1],
]);
