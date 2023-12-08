<?php

declare(strict_types=1);

/*
 * This file is part of the "brotkrueml/twig-codehighlight" package.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\TwigCodeHighlight\Tests;

use Brotkrueml\TwigCodeHighlight\Extension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ExtensionTest extends TestCase
{
    private Extension $subject;

    protected function setUp(): void
    {
        $this->subject = new Extension();
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

        yield 'with emphasize lines' => [
            'filterArguments' => '"python", emphasizeLines="3,5"',
            'code' => <<<PYTHON
def some_function():
    interesting = False
    print('This line is highlighted.')
    print('This one is not...')
    print('...but this one is.')
PYTHON,
            'expected' => <<<EXPECTED
<pre><code class="hljs python"><span class="hljs-function"><span class="hljs-keyword">def</span> <span class="hljs-title">some_function</span><span class="hljs-params">()</span>:</span>
    interesting = <span class="hljs-literal">False</span>
<span data-emphasize-line>    print(<span class="hljs-string">'This line is highlighted.'</span>)</span>
    print(<span class="hljs-string">'This one is not...'</span>)
<span data-emphasize-line>    print(<span class="hljs-string">'...but this one is.'</span>)</span></code></pre>
EXPECTED,
        ];

        yield 'with emphasize lines and line numbers' => [
            'filterArguments' => '"python", true, emphasizeLines="3,5"',
            'code' => <<<PYTHON
def some_function():
    interesting = False
    print('This line is highlighted.')
    print('This one is not...')
    print('...but this one is.')
PYTHON,
            'expected' => <<<EXPECTED
<pre><code class="hljs python"><span data-line-number="1"><span class="hljs-function"><span class="hljs-keyword">def</span> <span class="hljs-title">some_function</span><span class="hljs-params">()</span>:</span></span>
<span data-line-number="2">    interesting = <span class="hljs-literal">False</span></span>
<span data-emphasize-line><span data-line-number="3">    print(<span class="hljs-string">'This line is highlighted.'</span>)</span></span>
<span data-line-number="4">    print(<span class="hljs-string">'This one is not...'</span>)</span>
<span data-emphasize-line><span data-line-number="5">    print(<span class="hljs-string">'...but this one is.'</span>)</span></span></code></pre>
EXPECTED,
        ];

        yield 'with emphasizeLines is null' => [
            'filterArguments' => '"php", emphasizeLines=null',
            'code' => '<?php $var = 1; ?>',
            'expected' => '<pre><code class="hljs php"><span class="hljs-meta">&lt;?php</span> $var = <span class="hljs-number">1</span>; <span class="hljs-meta">?&gt;</span></code></pre>',
        ];

        yield 'with classes' => [
            'filterArguments' => '"php", classes="someclass anotherclass"',
            'code' => '<?php $var = 1; ?>',
            'expected' => '<pre class="someclass anotherclass"><code class="hljs php"><span class="hljs-meta">&lt;?php</span> $var = <span class="hljs-number">1</span>; <span class="hljs-meta">?&gt;</span></code></pre>',
        ];

        yield 'with classes with special chars' => [
            'filterArguments' => '"php", classes="some<class> \"another&class\'"',
            'code' => '<?php $var = 1; ?>',
            'expected' => '<pre class="some&lt;class&gt; &quot;another&amp;class&#039;"><code class="hljs php"><span class="hljs-meta">&lt;?php</span> $var = <span class="hljs-number">1</span>; <span class="hljs-meta">?&gt;</span></code></pre>',
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

        yield 'with a non-existing language with line numbers displays raw code block' => [
            'filterArguments' => '"nonexisting", true',
            'code' => 'I don\'t "exist" <here>',
            'expected' => '<pre><code><span data-line-number="1">I don&#039;t &quot;exist&quot; &lt;here&gt;</span></code></pre>',
        ];

        yield 'with a non-existing language with line numbers and start with line number displays raw code block' => [
            'filterArguments' => '"nonexisting", true, 10',
            'code' => 'I don\'t "exist" <here>',
            'expected' => '<pre><code><span data-line-number="10">I don&#039;t &quot;exist&quot; &lt;here&gt;</span></code></pre>',
        ];
    }

    #[Test]
    public function warningIsLoggedIfLanguageIsNotAvailable(): void
    {
        $logger = new class() implements LoggerInterface {
            use LoggerTrait;

            public string $level = '';
            public \Stringable|string $message = '';

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->level = $level;
                $this->message = $message;
            }
        };

        $subject = new Extension($logger);

        $loader = new ArrayLoader([
            'index' => '{{ "some code" | codehighlight("non_existing") }}',
        ]);
        $twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
        $twig->addExtension($subject);

        $template = $twig->load('index');

        $template->render();

        self::assertSame(LogLevel::WARNING, $logger->level);
        self::assertSame('Language "non_existing" is not available to highlight code', $logger->message);
    }

    #[Test]
    public function instantiatedWithLanguageAlias(): void
    {
        $subject = new Extension(languageAliases: [
            'text' => 'plaintext',
        ]);

        $loader = new ArrayLoader([
            'index' => '{{ "some text" | codehighlight("text") }}',
        ]);
        $twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
        $twig->addExtension($subject);

        $template = $twig->load('index');

        self::assertSame('<pre><code class="hljs plaintext">some text</code></pre>', $template->render());
    }

    #[Test]
    public function instantiatedWithClasses(): void
    {
        $subject = new Extension(classes: 'some-default-class');

        $loader = new ArrayLoader([
            'index' => '{{ "some text" | codehighlight("plaintext") }}',
        ]);
        $twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
        $twig->addExtension($subject);

        $template = $twig->load('index');

        self::assertSame('<pre class="some-default-class"><code class="hljs plaintext">some text</code></pre>', $template->render());
    }

    #[Test]
    public function instantiatedWithClassesAndClassesGivenViaFilter(): void
    {
        $subject = new Extension(classes: 'some-default-class');

        $loader = new ArrayLoader([
            'index' => '{{ "some text" | codehighlight("plaintext", classes="some-special-class") }}',
        ]);
        $twig = new Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
        $twig->addExtension($subject);

        $template = $twig->load('index');

        self::assertSame('<pre class="some-default-class some-special-class"><code class="hljs plaintext">some text</code></pre>', $template->render());
    }
}
