<?php

declare(strict_types=1);

namespace Warp\Container\Fixtures;

use Warp\Container\Exception\NotFoundException;
use Warp\Container\FactoryAggregateInterface;
use Warp\Container\FactoryInterface;
use Warp\Container\FactoryOptionsInterface;

final class ArrayFactoryAggregate implements FactoryAggregateInterface
{
    private array $factories;

    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    public function hasFactory(string $class): bool
    {
        return isset($this->factories[$class]);
    }

    public function getFactory(string $class): FactoryInterface
    {
        if (isset($this->factories[$class])) {
            return $this->factories[$class];
        }

        throw NotFoundException::factory($class);
    }

    public function make(string $class, array|FactoryOptionsInterface|null $options = null): mixed
    {
        return $this->getFactory($class)->make($options);
    }
}
