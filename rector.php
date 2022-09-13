<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(\array_merge(
        [
            __DIR__ . '/pkg/clock/src',
            __DIR__ . '/pkg/collection/src',
            __DIR__ . '/pkg/command-bus/src',
            __DIR__ . '/pkg/common/src',
            __DIR__ . '/pkg/container/src',
            __DIR__ . '/pkg/criteria/src',
            __DIR__ . '/pkg/cycle-bridge/src',
            __DIR__ . '/pkg/data-source/src',
            __DIR__ . '/pkg/dev-tool/src',
            __DIR__ . '/pkg/exception/src',
            __DIR__ . '/pkg/laminas-hydrator-bridge/src',
            __DIR__ . '/pkg/type/src',
            __DIR__ . '/pkg/value-object/src',
        ],
    ));

    // register a single rule
//    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);

    $rectorConfig->skip([
        UnionTypesRector::class => [
            __DIR__ . '/pkg/type/src/BuiltinType.php',
            __DIR__ . '/pkg/type/src/CollectionType.php',
        ],
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    //$rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon.dist');
};
