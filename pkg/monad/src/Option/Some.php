<?php

declare(strict_types=1);

namespace Warp\Monad\Option;

use Warp\Monad\Option;

/**
 * @template TValue
 * @implements Option<TValue>
 */
final class Some implements Option
{
    /**
     * @param TValue $value
     */
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    /**
     * @param TValue $value
     * @return self<TValue>
     */
    public static function new(mixed $value): self
    {
        return new self($value);
    }

    /**
     * @return true
     */
    public function isSome(): bool
    {
        return true;
    }

    /**
     * @return false
     */
    public function isNone(): bool
    {
        return false;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function getOrElse(mixed $default): mixed
    {
        return $this->get();
    }

    public function getOrCall(callable $callable): mixed
    {
        return $this->get();
    }

    public function orElse(Option $else): Option
    {
        return $this;
    }

    public function map(callable $fn): Option
    {
        return new self($fn($this->value, 0));
    }

    public function filter(callable $fn): Option
    {
        if ($fn($this->value, 0)) {
            return $this;
        }

        return None::none;
    }

    public function getIterator(): \Traversable
    {
        yield $this->get();
    }
}
