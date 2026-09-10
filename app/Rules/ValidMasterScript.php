<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Prüft, ob ein Master-Quelltext alle Platzhalter enthält, die der Update-Weg und das Wasserzeichen brauchen.
 */
class ValidMasterScript implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(__('skriptdepot.validation.master_script.header'));

            return;
        }

        if (preg_match('#// ==UserScript==.*?// ==/UserScript==#s', $value, $header) !== 1) {
            $fail(__('skriptdepot.validation.master_script.header'));

            return;
        }

        if (preg_match('/^\s*\/\/ @version\s+\{\{VERSION\}\}\s*$/m', $header[0]) !== 1) {
            $fail(__('skriptdepot.validation.master_script.version'));
        }

        if (preg_match('#^\s*// @updateURL\s+\{\{BASE\}\}/s/\{\{TOKEN\}\}/\{\{SLUG\}\}\.meta\.js\s*$#m', $header[0]) !== 1) {
            $fail(__('skriptdepot.validation.master_script.update_url'));
        }

        if (preg_match('#^\s*// @downloadURL\s+\{\{BASE\}\}/s/\{\{TOKEN\}\}/\{\{SLUG\}\}\.user\.js\s*$#m', $header[0]) !== 1) {
            $fail(__('skriptdepot.validation.master_script.download_url'));
        }

        if (substr_count($value, '{{BUILD}}') < 2) {
            $fail(__('skriptdepot.validation.master_script.build'));
        }
    }
}
