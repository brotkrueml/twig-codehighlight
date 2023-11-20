<?php

declare(strict_types=1);

/*
 * This file is part of the "brotkrueml/code-highlight-twig-extension" package.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\CodeHighlightTwigExtension;

use Highlight\Highlighter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @see \Brotkrueml\CodeHighlightTwigExtension\Tests\CodeHighlightTest
 */
final class CodeHighlight extends AbstractExtension
{
    private readonly Highlighter $highlighter;

    public function __construct()
    {
        $this->highlighter = new Highlighter();
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

    private function highlight(string $code, ?string $language, bool $showLineNumbers = false, int $startWithLineNumber = 1): string
    {
        try {
            if ($language === null) {
                throw new \DomainException();
            }

            $highlightedCode = $this->highlighter->highlight($language, $code);
            if ($showLineNumbers) {
                $highlightedCode->value = $this->appendLineNumbers($highlightedCode->value, $startWithLineNumber);
            }

            return \sprintf(
                '<pre><code class="hljs %s">%s</code></pre>',
                $highlightedCode->language,
                $highlightedCode->value,
            );
        } catch (\DomainException) {
            // This is thrown, if the specified language does not exist
            $code = \htmlentities($code);
            if ($showLineNumbers) {
                $code = $this->appendLineNumbers($code, $startWithLineNumber);
            }

            return \sprintf(
                '<pre><code>%s</code></pre>',
                $code,
            );
        }
    }

    private function appendLineNumbers(string $code, int $start = 1): string
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
}
