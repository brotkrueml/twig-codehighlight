<?php

declare(strict_types=1);

/*
 * This file is part of the "brotkrueml/code-highlight-twig-extension" package.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\CodeHighlightTwigExtension\Tests;

use Brotkrueml\CodeHighlightTwigExtension\CodeHighlight;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class CodeHighlightTest extends TestCase
{
    private CodeHighlight $subject;

    protected function setUp(): void
    {
        $this->subject = new CodeHighlight();
    }

    #[Test]
    public function getNameReturnExtensionName(): void
    {
        self::assertSame('codehighlight', $this->subject->getFilters()[0]->getName());
    }

    #[Test]
    #[DataProvider('providerForHighlightThroughTwigTemplate')]
    public function highlightThroughTwigTemplate(string $filterArguments, string $code, string $expected): void
    {
        $loader = new ArrayLoader([
            'index' => '{{ "' . \addcslashes($code, '"') . '" | codehighlight(' . $filterArguments . ') }}',
        ]);
        $twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
        $twig->addExtension($this->subject);

        $template = $twig->load('index');

        self::assertSame($expected, $template->render());
    }

    /**
     * @return \Iterator<string, array{filterArguments: string, code: string, expected: string}>
     */
    public static function providerForHighlightThroughTwigTemplate(): \Iterator
    {
        yield 'with language=html' => [
            'filterArguments' => '"html"',
            'code' => '<html lang="en"><head><title>Some HTML</title><body>Some<br>content</body></head>',
            'expected' => '<pre><code class="hljs xml"><span class="hljs-tag">&lt;<span class="hljs-name">html</span> <span class="hljs-attr">lang</span>=<span class="hljs-string">"en"</span>&gt;</span><span class="hljs-tag">&lt;<span class="hljs-name">head</span>&gt;</span><span class="hljs-tag">&lt;<span class="hljs-name">title</span>&gt;</span>Some HTML<span class="hljs-tag">&lt;/<span class="hljs-name">title</span>&gt;</span><span class="hljs-tag">&lt;<span class="hljs-name">body</span>&gt;</span>Some<span class="hljs-tag">&lt;<span class="hljs-name">br</span>&gt;</span>content<span class="hljs-tag">&lt;/<span class="hljs-name">body</span>&gt;</span><span class="hljs-tag">&lt;/<span class="hljs-name">head</span>&gt;</span></code></pre>',
        ];

        yield 'with language=php' => [
            'filterArguments' => '"php"',
            'code' => '<?php $var = 1; ?>',
            'expected' => '<pre><code class="hljs php"><span class="hljs-meta">&lt;?php</span> $var = <span class="hljs-number">1</span>; <span class="hljs-meta">?&gt;</span></code></pre>',
        ];

        yield 'with language=yaml as named argument' => [
            'filterArguments' => 'language="yaml"',
            'code' => <<<YAML
some:
  configuration:
    value: 42
YAML,
            'expected' => <<<EXPECTED
<pre><code class="hljs yaml"><span class="hljs-attr">some:</span>
  <span class="hljs-attr">configuration:</span>
    <span class="hljs-attr">value:</span> <span class="hljs-number">42</span></code></pre>
EXPECTED,
        ];

        yield 'with line numbers' => [
            'filterArguments' => '"php", true',
            'code' => <<<PHP
\$foo = 1;
\$bar = 42;
PHP,
            'expected' => <<<EXPECTED
<pre><code class="hljs php"><span data-line-number="1">\$foo = <span class="hljs-number">1</span>;</span>
<span data-line-number="2">\$bar = <span class="hljs-number">42</span>;</span></code></pre>
EXPECTED,
        ];

        yield 'with line numbers and start with line number' => [
            'filterArguments' => '"php", true, 11',
            'code' => <<<PHP
\$foo = 1;
\$bar = 42;
PHP,
            'expected' => <<<EXPECTED
<pre><code class="hljs php"><span data-line-number="11">\$foo = <span class="hljs-number">1</span>;</span>
<span data-line-number="12">\$bar = <span class="hljs-number">42</span>;</span></code></pre>
EXPECTED,
        ];

        yield 'without line numbers, but start with line number, then no line numbers displayed' => [
            'filterArguments' => 'language="php", startWithLineNumber=11',
            'code' => <<<PHP
\$foo = 1;
\$bar = 42;
PHP,
            'expected' => <<<EXPECTED
<pre><code class="hljs php">\$foo = <span class="hljs-number">1</span>;
\$bar = <span class="hljs-number">42</span>;</code></pre>
EXPECTED,
        ];

        yield 'with a null value as language' => [
            'filterArguments' => 'null',
            'code' => 'Some code',
            'expected' => '<pre><code>Some code</code></pre>',
        ];

        yield 'with a non-existing language displays raw code block' => [
            'filterArguments' => '"nonexisting"',
            'code' => 'I don\'t "exist" <here>',
            'expected' => '<pre><code>I don&#039;t &quot;exist&quot; &lt;here&gt;</code></pre>',
        ];
    }
}
