<?php

declare(strict_types=1);

namespace Warp\CommandBus\Mapping\Method;

final class StaticMethodNameMapping implements MethodNameMappingInterface
{
    public function __construct(
        private readonly string $methodName,
    ) {
    }

    public function getMethodName(string $commandClass): string
    {
        return $this->methodName;
    }
}
