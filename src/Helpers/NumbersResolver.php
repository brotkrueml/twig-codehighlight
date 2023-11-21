<?php

declare(strict_types=1);

/*
 * This file is part of the "brotkrueml/twig-codehighlight" package.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\TwigCodeHighlight\Helpers;

/**
 * Resolves a string ofnumbers, for example, "1-3,5,8-10,12" to an array
 * of all numbers, for example, [1,2,3,5,8,9,10,12].
 *
 * @internal
 * @see \Brotkrueml\TwigCodeHighlight\Tests\Helpers\NumbersResolverTest
 */
final class NumbersResolver
{
    /**
     * @return list<int>
     */
    public function resolve(string $numbers): array
    {
        if ($numbers === '') {
            return [];
        }

        $numbersArray = \explode(',', $numbers);
        $separatedNumbers = [];
        foreach ($numbersArray as $numberWithPossibleRange) {
            if (\is_numeric($numberWithPossibleRange)) {
                $separatedNumbers[] = (int)$numberWithPossibleRange;
                continue;
            }
            if (! \str_contains($numberWithPossibleRange, '-')) {
                // Neither a number nor a range
                continue;
            }
            [$low, $high] = \explode('-', $numberWithPossibleRange);
            if (! \is_numeric($low)) {
                continue;
            }
            if (! \is_numeric($high)) {
                continue;
            }
            $separatedNumbers = [...$separatedNumbers, ...\range((int)$low, (int)$high)];
        }

        \sort($separatedNumbers);

        return \array_values(\array_unique($separatedNumbers, \SORT_NUMERIC));
    }
}
