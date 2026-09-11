<?php

use App\Models\User;
use App\Notifications\UserInvitation;

it('verwendet im Mail-HTML keine schlecht unterstützten CSS-Eigenschaften', function () {
    $user = User::factory()->make(['name' => 'Erika', 'locale' => 'de']);

    $html = (string) (new UserInvitation('https://portal.example.test/portal/invitation/1?signature=x', 7))->toMail($user)->render();

    expect($html)
        ->not->toContain('position:')
        ->not->toContain('box-sizing')
        ->not->toContain('!important')
        ->not->toContain('@media')
        ->toContain('<table');
});
