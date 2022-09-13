<?php

declare(strict_types=1);

namespace Warp\CommandBus\Mapping;

use Warp\CommandBus\Mapping\ClassName\ClassNameMappingInterface;
use Warp\CommandBus\Mapping\Method\MethodNameMappingInterface;

final class CompositeMapping implements CommandToHandlerMappingInterface
{
    public function __construct(
        private readonly ClassNameMappingInterface $classNameMapping,
        private readonly MethodNameMappingInterface $methodNameMapping,
    ) {
    }

    public function getClassName(string $commandClass): string
    {
        return $this->classNameMapping->getClassName($commandClass);
    }

    public function getMethodName(string $commandClass): string
    {
        return $this->methodNameMapping->getMethodName($commandClass);
    }
}
