# Twig extension for code highlighting

[![CI Status](https://github.com/brotkrueml/schema/workflows/CI/badge.svg?branch=main)](https://github.com/brotkrueml/schema/actions?query=workflow%3ACI)

This package provides a Twig extension for code highlighting. Under the hood, the
[scrivo/highlight.php](https://github.com/scrivo/highlight.php) package is used
which does the hard work.

An addition to the highlighting of code this Twig extension provides additional
(opinionated) features:

- [line numbers](#line-numbers)
- [emphasize lines](#emphasize-lines)

> This package is under heavy development!

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

You can also use named arguments, the example above can be also written like:

```twig
{{ "<?php echo 'test'; ?>" | codehighlight(language="php") }}
```


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
