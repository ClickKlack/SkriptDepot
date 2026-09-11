<?php

namespace App\Services;

use App\Exceptions\ScriptSyntaxException;
use Peast\Peast;
use Peast\Syntax\Exception as PeastException;
use Peast\Syntax\Node\BlockStatement;
use Peast\Syntax\Node\ExpressionStatement;
use Peast\Syntax\Node\Node;
use Peast\Syntax\Node\Program;
use Peast\Syntax\Node\Statement;
use Peast\Syntax\Node\StringLiteral;
use RuntimeException;

/**
 * Macht aus einem rohen Userscript den Master-Quelltext mit Platzhaltern.
 *
 * Der Header bekommt {{VERSION}} sowie personalisierte Update- und Download-URLs. In den Code kommen
 * ein Build-Kommentar nach dem Header, eine harmlose Zuweisung am Anfang und, je nach Länge, weitere
 * Build-Kommentare an Anweisungsgrenzen. Die Grenzen liefert ein JavaScript-Parser, damit kein Marker
 * in einem mehrzeiligen String oder Blockkommentar landet. Der Code selbst wird nicht umgeschrieben.
 */
class ScriptWatermarker
{
    private const string HEADER_PATTERN = '#// ==UserScript==.*?// ==/UserScript==#s';

    private const string HEADER_COMMENT = '/* build: {{BUILD}} */';

    private const string MARKER_COMMENT = '// build: {{BUILD}}';

    private const string ASSIGNMENT = "globalThis.__skriptdepotBuild = '{{BUILD}}';";

    public function hasHeader(string $source): bool
    {
        return preg_match(self::HEADER_PATTERN, $source) === 1;
    }

    /**
     * Versionsnummer aus dem Header, sofern dort eine konkrete steht und nicht der Platzhalter.
     */
    public function detectVersion(string $source): ?string
    {
        if (preg_match(self::HEADER_PATTERN, $source, $match) !== 1) {
            return null;
        }

        if (preg_match('#^\s*//\s*@version\s+(\S+)\s*$#m', $match[0], $version) !== 1) {
            return null;
        }

        return $version[1] === '{{VERSION}}' ? null : $version[1];
    }

    /**
     * @throws ScriptSyntaxException wenn das Skript kein gültiges JavaScript ist
     * @throws RuntimeException wenn der ==UserScript==-Block fehlt
     */
    public function inject(string $source): string
    {
        $source = str_replace(["\r\n", "\r"], "\n", $source);

        // Bereits vorbereitete Quelltexte bleiben unverändert, damit ein zweiter Lauf nichts verdoppelt.
        if (str_contains($source, '{{BUILD}}')) {
            return $source;
        }

        if (preg_match(self::HEADER_PATTERN, $source, $match, PREG_OFFSET_CAPTURE) !== 1) {
            throw new RuntimeException('Der Quelltext enthält keinen ==UserScript==-Block.');
        }

        $before = substr($source, 0, $match[0][1]);
        $header = $this->normalizeHeader($match[0][0]);
        $body = substr($source, $match[0][1] + strlen($match[0][0]));
        // Versatz anhand des Original-Headers, damit Fehlerzeilen zum eingefügten Text passen.
        $bodyLineOffset = substr_count($before.$match[0][0], "\n");

        $totalLines = substr_count($source, "\n") + 1;
        $body = $this->injectIntoBody($body, $totalLines, $bodyLineOffset);

        $result = $before.$header."\n".self::HEADER_COMMENT.$body;

        // Sicherheitsnetz: das Ergebnis muss weiterhin gültiges JavaScript sein.
        $this->parse($result, 0);

        return $result;
    }

    private function normalizeHeader(string $header): string
    {
        $lines = explode("\n", $header);
        $column = $this->detectValueColumn($lines);

        $wanted = [
            'version' => '{{VERSION}}',
            'updateURL' => '{{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js',
            'downloadURL' => '{{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js',
        ];

        foreach ($wanted as $key => $value) {
            $replaced = false;

            foreach ($lines as $index => $line) {
                if (preg_match('#^(\s*//\s*@'.preg_quote($key, '#').'\s+).*$#', $line, $parts) === 1) {
                    $lines[$index] = $parts[1].$value;
                    $replaced = true;
                }
            }

            if (! $replaced) {
                $keyPart = '// @'.$key;
                $padding = max(1, $column - strlen($keyPart));
                array_splice($lines, count($lines) - 1, 0, [$keyPart.str_repeat(' ', $padding).$value]);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Spalte, in der die Werte des Headers beginnen, damit eingefügte Zeilen bündig sind.
     *
     * @param  list<string>  $lines
     */
    private function detectValueColumn(array $lines): int
    {
        foreach ($lines as $line) {
            if (preg_match('#^(//\s*@\w+\s+)\S#', $line, $parts) === 1) {
                return strlen($parts[1]);
            }
        }

        return 17;
    }

    private function injectIntoBody(string $body, int $totalLines, int $bodyLineOffset): string
    {
        $program = $this->parse($body, $bodyLineOffset);
        $lines = explode("\n", $body);

        $safeLines = $this->safeLines($program, $lines);
        $insertions = [];

        $assignmentLine = $this->assignmentLine($program, $lines);

        if ($assignmentLine !== null) {
            $insertions[$assignmentLine][] = self::ASSIGNMENT;
        } else {
            $lines[] = self::ASSIGNMENT;
        }

        foreach ($this->markerLines($safeLines, count($lines), $totalLines) as $line) {
            $insertions[$line][] = self::MARKER_COMMENT;
        }

        krsort($insertions);

        foreach ($insertions as $line => $snippets) {
            $indent = $this->indentOf($lines[$line - 1]);
            $inserted = array_map(fn (string $snippet): string => $indent.$snippet, $snippets);
            array_splice($lines, $line - 1, 0, $inserted);
        }

        return implode("\n", $lines);
    }

    /**
     * Zeilen (1-basiert), vor denen gefahrlos eine Kommentarzeile eingefügt werden kann:
     * jede Anweisung, die als erstes auf ihrer Zeile beginnt.
     *
     * @param  list<string>  $lines
     * @return list<int>
     */
    private function safeLines(Program $program, array $lines): array
    {
        $safe = [];

        $program->traverse(function (Node $node) use (&$safe, $lines): void {
            if (! $node instanceof Statement || $node instanceof BlockStatement) {
                return;
            }

            $start = $node->getLocation()->getStart();
            $line = $start->getLine();
            $lineText = $lines[$line - 1] ?? '';

            if (strlen($lineText) - strlen(ltrim($lineText)) === $start->getColumn()) {
                $safe[$line] = $line;
            }
        });

        sort($safe);

        return array_values($safe);
    }

    /**
     * Zeile der ersten Anweisung auf oberster Ebene, die keine Direktive wie 'use strict' ist.
     *
     * @param  list<string>  $lines
     */
    private function assignmentLine(Program $program, array $lines): ?int
    {
        foreach ($program->getBody() as $statement) {
            if ($statement instanceof ExpressionStatement && $statement->getExpression() instanceof StringLiteral) {
                continue;
            }

            $start = $statement->getLocation()->getStart();
            $lineText = $lines[$start->getLine() - 1] ?? '';

            if (strlen($lineText) - strlen(ltrim($lineText)) === $start->getColumn()) {
                return $start->getLine();
            }
        }

        return null;
    }

    /**
     * Verteilt die Marker gleichmäßig über den Code und rückt jeden auf die nächstgelegene sichere Zeile.
     *
     * @param  list<int>  $safeLines
     * @return list<int>
     */
    private function markerLines(array $safeLines, int $bodyLines, int $totalLines): array
    {
        if ($safeLines === []) {
            return [];
        }

        $config = config('skriptdepot.watermark');
        $count = max(
            (int) $config['min_markers'],
            min((int) $config['max_markers'], intdiv($totalLines, (int) $config['lines_per_marker'])),
        );

        $chosen = [];

        for ($index = 1; $index <= $count; $index++) {
            $target = (int) round($index * $bodyLines / ($count + 1));
            $nearest = $safeLines[0];

            foreach ($safeLines as $line) {
                if (abs($line - $target) <= abs($nearest - $target)) {
                    $nearest = $line;
                }
            }

            $chosen[$nearest] = $nearest;
        }

        sort($chosen);

        return array_values($chosen);
    }

    private function indentOf(string $line): string
    {
        return substr($line, 0, strlen($line) - strlen(ltrim($line)));
    }

    /**
     * @throws ScriptSyntaxException
     */
    private function parse(string $source, int $lineOffset): Program
    {
        try {
            return Peast::latest($source, ['sourceType' => Peast::SOURCE_TYPE_SCRIPT])->parse();
        } catch (PeastException $exception) {
            throw new ScriptSyntaxException($exception->getMessage(), $lineOffset + $exception->getPosition()->getLine());
        }
    }
}
