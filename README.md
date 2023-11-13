# Twig extension for code highlighting

[![CI Status](https://github.com/brotkrueml/schema/workflows/CI/badge.svg?branch=main)](https://github.com/brotkrueml/schema/actions?query=workflow%3ACI)

This package provides a Twig extension for code highlighting. Under the hood, the
[scrivo/highlight.php](https://github.com/scrivo/highlight.php) package is used
which does the hard work.

> This package is under heavy development!

## Usage

Add the extension to the Twig environment:

```php
$twig->addExtension(new Brotkrueml\CodeHighlightTwigExtension\CodeHighlight());
```

Use it in Twig templates:
```twig
{{ "<?php echo 'test'; ?>" | codehighlight("php") }}
```
