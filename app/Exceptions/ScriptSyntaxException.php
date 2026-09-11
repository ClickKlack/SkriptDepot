<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Das eingefügte Skript ist kein gültiges JavaScript; die Zeile bezieht sich auf den gesamten Quelltext.
 */
class ScriptSyntaxException extends RuntimeException
{
    public function __construct(string $message, public readonly int $sourceLine)
    {
        parent::__construct($message);
    }
}
