# Twig extension for code highlighting

[![CI Status](https://github.com/brotkrueml/schema/workflows/CI/badge.svg?branch=main)](https://github.com/brotkrueml/schema/actions?query=workflow%3ACI)

This package provides a Twig extension for server-side code highlighting. Under the
hood, the [scrivo/highlight.php](https://github.com/scrivo/highlight.php) package is
used which does the hard work. You can use every
[theme provided for highlight.js](https://highlightjs.org/demo).

An addition to the highlighting of code this Twig extension provides additional
(opinionated) features:

- [language aliases](#language-aliases)
- [line numbers](#line-numbers)
- [emphasize lines](#emphasize-lines)
- [classes](#classes)

> This package is currently under heavy development!

## Usage

Add the extension to the Twig environment:

```php
$twig->addExtension(new Brotkrueml\TwigCodeHighlight\Extension());
```

Use it in Twig templates:
```twig
{{ "<?php echo 'test'; ?>" | codehighlight("php") }}
```

If the language is not available, a raw code block is displayed.

It is also possible to inject a logger that implements `\Psr\Log\LoggerInterface`
to display warnings when a given language is not available, either via dependency
injection or manually:

```php
$twig->addExtension(new Brotkrueml\TwigCodeHighlight\Extension($myLogger));
```

You can also use named arguments, the example above can be also written like:

```twig
{{ "<?php echo 'test'; ?>" | codehighlight(language="php") }}
```

This will render something like this:

```html
<pre><code class="hljs php"><span class="hljs-meta">&lt;?php</span> <span class="hljs-keyword">echo</span> <span class="hljs-string">"test"</span>; <span class="hljs-meta">?&gt;</span></code></pre>
```

### Language aliases

When you have already an existing application with languages named alternatively than highlight.php
provides them, you can assign an array of language aliases when instantiating the extension class:

```php
$twig->addExtension(new Brotkrueml\TwigCodeHighlight\Extension(languageAliases: ['text' => 'plaintext', 'sh' => 'shell']));
```

In this example, we introduce `text` as an alias for `plaintext` and `sh` for `shell`.


### Line numbers

By default, no line numbers are displayed. You can switch them one by setting the second argument:

Use it in Twig templates:
```twig
{{ "<?php echo 'test'; ?>" | codehighlight(language="php", showLineNumbers=true) }}
```

Line numbers start with `1`, but can also give a custom start number with another argument:

```twig
{{ "<?php echo 'test'; ?>" | codehighlight(language="php", showLineNumbers=true, startWithLineNumber=11) }}
```

This adds a `<span data-line-number="x">...</span>` to each line, where `x` is the increasing line number.

You can then use a CSS rule to display the line number, for example:

```css
code [data-line-number]::before {
    content: attr(data-line-number);
    display: inline-block;
    margin-right: 1em;
    text-align: right;
    width: 2ch;
}
```

### Emphasize lines

You can emphasize lines which highlights one or more lines in a code snippet.

Use it in Twig templates:
```twig
{{ code | codehighlight(language="php", emphasizeLines="1-3,5") }}
```

This example emphasizes the lines 1,2,3 and 5.

This adds a `<span data-emphasize-line>...</span>` to each line which should be emphasized.

You can then use a custom CSS rule to highlight the line, for example:

```css
code [data-emphasize-line] {
    background: lightcyan;
}
```
