<?php

use App\Actions\SendUserInvitation;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Notification;

it('verschickt die Einladung mit signiertem Link und merkt sich den Zeitpunkt', function () {
    Notification::fake();
    $this->freezeSecond();
    $user = User::factory()->unverified()->create();

    app(SendUserInvitation::class)->handle($user);

    expect($user->fresh()->invited_at)->toEqual(now());
    Notification::assertSentTo($user, UserInvitation::class, function (UserInvitation $notification) use ($user): bool {
        return str_contains($notification->acceptUrl, "/portal/invitation/{$user->id}?expires=")
            && str_contains($notification->acceptUrl, 'signature=')
            && $notification->validDays === 7;
    });
});
