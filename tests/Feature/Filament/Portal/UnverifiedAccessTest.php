<?php

use App\Models\User;

it('verweigert Nutzern ohne angenommene Einladung den Zugang zum Portal', function () {
    $this->actingAs(User::factory()->unverified()->create())->get('/portal/scripts')->assertForbidden();
});
