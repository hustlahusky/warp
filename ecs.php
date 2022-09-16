<?php

declare(strict_types=1);

namespace Warp;

use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ECSConfig $config): void {
    $config->parameters()->set(Option::CACHE_DIRECTORY, __DIR__ . '/._ecs_cache');
    $config->parallel();

    $config->paths(\array_merge(
        \glob(__DIR__ . '/pkg/*/src') ?: [],
        \glob(__DIR__ . '/pkg/*/bin') ?: [],
        [
            __DIR__ . '/warp',
        ]
    ));

    $config->skip([
        'Unused variable $_.' => null,
        'Unused parameter $_.' => null,

        StrictComparisonFixer::class => [
            __DIR__ . '/pkg/laminas-hydrator-bridge/src/Strategy/BooleanStrategy.php',
        ],
        'Class LoggerMiddleware contains unused private method compareLogLevel().' => [
            __DIR__ . '/pkg/command-bus/src/Middleware/Logger/LoggerMiddleware.php',
        ],
        VisibilityRequiredFixer::class => [
            __DIR__ . '/pkg/common/src/Kernel/ConsoleApplicationConfiguratorTrait.php',
        ],
        LineLengthFixer::class => [
            __DIR__ . '/pkg/common/src/Kernel/AbstractKernel.php',
        ],
    ]);

    $config->import(__DIR__ . '/vendor/getwarp/easy-coding-standard-bridge/resources/config/warp.php', null, 'not_found');
    $config->import(__DIR__ . '/pkg/easy-coding-standard-bridge/resources/config/warp.php', null, 'not_found');
    $config->import(__DIR__ . '/ecs-baseline.php', null, 'not_found');
};
