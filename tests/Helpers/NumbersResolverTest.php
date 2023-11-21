<?php

declare(strict_types=1);

/*
 * This file is part of the "brotkrueml/twig-codehighlight" package.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Brotkrueml\TwigCodeHighlight\Tests\Helpers;

use Brotkrueml\TwigCodeHighlight\Helpers\NumbersResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NumbersResolverTest extends TestCase
{
    private NumbersResolver $subject;

    protected function setUp(): void
    {
        $this->subject = new NumbersResolver();
    }

    /**
     * @param list<int> $expected
     */
    #[Test]
    #[DataProvider('providerForResolve')]
    public function resolve(string $numbers, array $expected): void
    {
        $actual = $this->subject->resolve($numbers);

        self::assertSame($expected, $actual);
    }

    public static function providerForResolve(): \Iterator
    {
        yield 'empty string returns empty array' => [
            'numbers' => '',
            'expected' => [],
        ];

        yield 'with one number' => [
            'numbers' => '1',
            'expected' => [1],
        ];

        yield 'with one range' => [
            'numbers' => '1-3',
            'expected' => [1, 2, 3],
        ];

        yield 'with a list of different numbers' => [
            'numbers' => '1,3,5',
            'expected' => [1, 3, 5],
        ];

        yield 'with two identical numbers' => [
            'numbers' => '1,1',
            'expected' => [1],
        ];

        yield 'with two identical ranges' => [
            'numbers' => '1-2, 1-2',
            'expected' => [1, 2],
        ];

        yield 'with multiple ranges and numbers' => [
            'numbers' => '1-3,5,8-10,12',
            'expected' => [1, 2, 3, 5, 8, 9, 10, 12],
        ];

        yield 'with multiple ranges and numbers in a random order' => [
            'numbers' => '5,8-10,1-3,12',
            'expected' => [1, 2, 3, 5, 8, 9, 10, 12],
        ];

        yield 'with a negative number is acceptable' => [
            'numbers' => '-3',
            'expected' => [-3],
        ];

        yield 'with empty elements in a list filtered out' => [
            'numbers' => ',1,,3,',
            'expected' => [1, 3],
        ];

        yield 'with empty elements and spaces in a list filtered out' => [
            'numbers' => ' ,1 ,   , 3 , ',
            'expected' => [1, 3],
        ];

        yield 'with a range and spaces' => [
            'numbers' => ' 1 - 3 ',
            'expected' => [1, 2, 3],
        ];

        yield 'with a number and a letter' => [
            'numbers' => '1a',
            'expected' => [],
        ];

        yield 'with a wrong number and a letter in the beginning of the range' => [
            'numbers' => '1a-5,9',
            'expected' => [9],
        ];

        yield 'with a wrong number and a letter at the end of the range' => [
            'numbers' => '7-9b,15',
            'expected' => [15],
        ];

        yield 'with a negative range of numbers' => [
            'numbers' => '-2-3',
            'expected' => [],
        ];
    }
}
