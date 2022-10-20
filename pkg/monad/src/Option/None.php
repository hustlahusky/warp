<?php

declare(strict_types=1);

namespace Warp\Monad\Option;

use Warp\Monad\Option;

/**
 * @implements Option<mixed>
 */
enum None implements Option
{
    case none;

    /**
     * @return false
     */
    public function isSome(): bool
    {
        return false;
    }

    /**
     * @return true
     */
    public function isNone(): bool
    {
        return true;
    }

    public function get(): never
    {
        throw new \RuntimeException('None has no value.');
    }

    public function getOrElse(mixed $default): mixed
    {
        return $default;
    }

    public function getOrCall(callable $callable): mixed
    {
        return $callable();
    }

    public function orElse(Option $else): Option
    {
        return $else;
    }

    public function map(callable $fn): Option
    {
        return $this;
    }

    public function filter(callable $fn): Option
    {
        return $this;
    }

    public function getIterator(): \Traversable
    {
        return new \EmptyIterator();
    }
}
