<?php

declare(strict_types=1);

namespace Warp\CommandBus\Mapping\ClassName;

final class SuffixClassNameMapping implements ClassNameMappingInterface
{
    public function __construct(
        private readonly string $suffix,
    ) {
    }

    public function getClassName(string $commandClass): string
    {
        return $commandClass . $this->suffix;
    }
}
