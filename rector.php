<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $config): void {
    $config->phpVersion(PhpVersion::PHP_81);

    $config->import(LevelSetList::UP_TO_PHP_81);
    $config->import(PHPUnitSetList::PHPUNIT_100);
    $config->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);
    $config->import(SetList::CODE_QUALITY);
    $config->import(SetList::DEAD_CODE);
    $config->import(SetList::EARLY_RETURN);
    $config->import(SetList::TYPE_DECLARATION);

    $config->autoloadPaths([
        __DIR__ . '/vendor/autoload.php',
    ]);
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $config->skip([
        PreferPHPUnitThisCallRector::class,
    ]);
};
