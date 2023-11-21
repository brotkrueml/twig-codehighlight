<?php

declare(strict_types=1);

/*
 * This file is part of the "brotkrueml/twig-codehighlight" package.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\TwigCodeHighlight;

use Brotkrueml\TwigCodeHighlight\Parser\LineNumbersParser;
use Highlight\Highlighter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @see \Brotkrueml\TwigCodeHighlight\Tests\ExtensionTest
 */
final class Extension extends AbstractExtension
{
    private readonly Highlighter $highlighter;
    private readonly LineNumbersParser $lineNumbersParser;

    private string $language;
    private bool $showLineNumbers;
    private int $startWithLineNumber;
    private string $emphasizeLines;

    public function __construct()
    {
        $this->highlighter = new Highlighter();
        $this->lineNumbersParser = new LineNumbersParser();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'codehighlight',
                $this->highlight(...),
                [
                    'is_safe' => ['html'],
                ],
            ),
        ];
    }

    private function highlight(
        string $code,
        ?string $language,
        bool $showLineNumbers = false,
        int $startWithLineNumber = 1,
        string $emphasizeLines = '',
    ): string {
        $this->language = $language ?? '';
        $this->showLineNumbers = $showLineNumbers;
        $this->startWithLineNumber = $startWithLineNumber;
        $this->emphasizeLines = $emphasizeLines;

        if ($this->language === '') {
            return $this->buildHtmlCode($code);
        }

        try {
            $highlightedCode = $this->highlighter->highlight($this->language, $code);
            $codeClasses = 'hljs ' . $highlightedCode->language;

            return $this->buildHtmlCode($highlightedCode->value, false, $codeClasses);
        } catch (\DomainException) {
            // This is thrown, if the specified language does not exist
            return $this->buildHtmlCode($code);
        }
    }

    private function buildHtmlCode(string $code, bool $encode = true, string $codeClasses = ''): string
    {
        if ($encode) {
            $code = \htmlentities($code);
        }

        if ($this->showLineNumbers) {
            $code = $this->addLineNumbers($code, $this->startWithLineNumber);
        }

        if ($this->emphasizeLines !== '') {
            $code = $this->addEmphasizeLines($code);
        }

        return \sprintf(
            '<pre><code%s>%s</code></pre>',
            $codeClasses !== '' ? ' class="' . $codeClasses . '"' : '',
            $code,
        );
    }

    private function addLineNumbers(string $code, int $start = 1): string
    {
        $lines = \explode("\n", $code);
        $lineCounter = $start;
        $newLines = \array_map(static function (string $line) use (&$lineCounter): string {
            return \sprintf(
                '<span data-line-number="%d">%s</span>',
                $lineCounter++,
                $line,
            );
        }, $lines);

        return \implode("\n", $newLines);
    }

    private function addEmphasizeLines(string $code): string
    {
        $parsedEmphasizedLines = $this->lineNumbersParser->parse($this->emphasizeLines);
        if ($parsedEmphasizedLines === []) {
            return $code;
        }

        $lines = \explode("\n", $code);
        $lineNumber = 1;
        $newLines = [];
        foreach ($lines as $line) {
            if (\in_array($lineNumber, $parsedEmphasizedLines, true)) {
                $newLines[] = \sprintf(
                    '<span data-emphasize-line>%s</span>',
                    $line,
                );
            } else {
                $newLines[] = $line;
            }

            $lineNumber++;
        }

        return \implode("\n", $newLines);
    }
}
