<?php

declare(strict_types=1);

namespace Warp\CommandBus\Mapping;

use Warp\CommandBus\Exception\FailedToMapCommandException;

final class MapByStaticList implements CommandToHandlerMappingInterface
{
    /**
     * @param array<class-string,array{class-string,string}> $mapping
     * @example
     *     ```php
     *     new MapByStaticList([
     *         SomeCommand::class => [SomeHandler::class, 'handle'],
     *         OtherCommand::class => [WhateverHandler::class, 'handleOtherCommand'],
     *         ...
     *     ])
     *     ```
     */
    public function __construct(
        private readonly array $mapping,
    ) {
    }

    public function getClassName(string $commandClass): string
    {
        if (!\array_key_exists($commandClass, $this->mapping)) {
            throw FailedToMapCommandException::className($commandClass);
        }

        return $this->mapping[$commandClass][0];
    }

    public function getMethodName(string $commandClass): string
    {
        if (!\array_key_exists($commandClass, $this->mapping)) {
            throw FailedToMapCommandException::methodName($commandClass);
        }

        return $this->mapping[$commandClass][1];
    }
}
