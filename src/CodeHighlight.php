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

    private function highlight(string $code, string $languageName): string
    {
        try {
            $highlightedCode = $this->highlighter->highlight($languageName, $code);

            return '<pre><code class="hljs ' . $highlightedCode->language . '">'
                . $highlightedCode->value
                . '</code></pre>';
        } catch (\DomainException) {
            // This is thrown, if the specified language does not exist
            return '<pre><code>'
                . \htmlentities($code)
                . '</code></pre>';
        }
    }
}
