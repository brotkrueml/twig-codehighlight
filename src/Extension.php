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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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

    /**
     * @param array<string, string> $languageAliases
     * @param list<array{0: string, 1: string, 2?: bool}> $additionalLanguages
     */
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly array $languageAliases = [],
        array $additionalLanguages = [],
        private string $classes = '',
    ) {
        $this->highlighter = new Highlighter();
        $this->lineNumbersParser = new LineNumbersParser();

        foreach ($additionalLanguages as $language) {
            $this->highlighter::registerLanguage($language[0], $language[1], $language[2] ?? false);
        }
    }

    /**
     * @return list<TwigFilter>
     */
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

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'codehighlight_languages',
                $this->languages(...),
            ),
        ];
    }

    private function highlight(
        string $code,
        ?string $language,
        bool $showLineNumbers = false,
        int $startWithLineNumber = 1,
        ?string $emphasizeLines = '',
        string $classes = '',
    ): string {
        $this->language = $this->languageAliases[$language] ?? $language ?? '';
        $this->showLineNumbers = $showLineNumbers;
        $this->startWithLineNumber = $startWithLineNumber;
        $this->emphasizeLines = $emphasizeLines ?? '';
        $this->classes = \trim($this->classes . ' ' . $classes);

        if ($this->language === '') {
            return $this->buildHtmlCode($code);
        }

        try {
            $highlightedCode = $this->highlighter->highlight($this->language, $code);
            $codeClasses = 'hljs ' . $highlightedCode->language;

            return $this->buildHtmlCode($highlightedCode->value, false, $codeClasses);
        } catch (\DomainException) {
            // This is thrown, if the specified language does not exist
            $this->logger->warning(
                \sprintf(
                    'Language "%s" is not available to highlight code',
                    $this->language,
                ),
            );

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
            '<pre%s><code%s>%s</code></pre>',
            $this->classes !== '' ? ' class="' . \htmlentities($this->classes) . '"' : '',
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

    /**
     * @return list<string>
     */
    private function languages(): array
    {
        $registeredLanguages = Highlighter::listRegisteredLanguages();
        \sort($registeredLanguages);

        return $registeredLanguages;
    }
}
