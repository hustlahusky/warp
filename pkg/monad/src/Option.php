<?php

declare(strict_types=1);

namespace Warp\Monad;

/**
 * @template TValue
 * @extends \IteratorAggregate<int,TValue>
 */
interface Option extends \IteratorAggregate
{
    public function isSome(): bool;

    public function isNone(): bool;

    /**
     * @return TValue
     */
    public function get(): mixed;

    /**
     * @template TDefault
     * @param TDefault $default
     * @return TValue|TDefault
     */
    public function getOrElse(mixed $default): mixed;

    /**
     * @template TDefault
     * @param callable():TDefault $callable
     * @return TValue|TDefault
     */
    public function getOrCall(callable $callable): mixed;

    /**
     * @param Option<TValue> $else
     * @return Option<TValue>
     */
    public function orElse(self $else): self;

    /**
     * Applies the callable to the value of the option if it is non-empty,
     * and returns the return value of the callable wrapped in Some().
     *
     * If the option is empty, then the callable is not applied.
     *
     * @template TNewValue
     * @param callable(TValue=,array-key):TNewValue $fn
     * @return Option<TNewValue>
     */
    public function map(callable $fn): self;

    /**
     * If the option is empty, it is returned immediately without applying the callable.
     *
     * If the option is non-empty, the callable is applied, and if it returns true,
     * the option itself is returned; otherwise, None is returned.
     *
     * @param callable(TValue=,array-key):bool $fn
     * @return Option<TValue>
     */
    public function filter(callable $fn): self;
}
