<?php

use Illuminate\Support\Facades\Route;

// Die Startseite hat keinen eigenen Inhalt: Besucher landen direkt im Nutzerportal.
Route::redirect('/', '/portal')->name('home');
