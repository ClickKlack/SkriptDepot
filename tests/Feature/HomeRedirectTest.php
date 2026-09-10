<?php

// Die Startseite muss immer auf das Nutzerportal weiterleiten.
test('die Startseite leitet auf das Portal weiter', function () {
    $this->get('/')->assertRedirect('/portal');
});
