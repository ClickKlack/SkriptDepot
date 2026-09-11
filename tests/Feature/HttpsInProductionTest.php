<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;

it('erzeugt produktiv nur HTTPS-Links', function () {
    $this->app->detectEnvironment(fn () => 'production');
    (new AppServiceProvider($this->app))->boot();

    expect(URL::to('/portal'))->toStartWith('https://');
});
