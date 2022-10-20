<?php

declare(strict_types=1);

namespace Warp\Monad;

/**
 * @template TValue
 * @param Option<TValue>|TValue $value
 * @param mixed $noneValue
 * @return Option<TValue>
 */
function optional(mixed $value, mixed $noneValue = null): Option
{
    return Option\optional($value, $noneValue);
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $elements
 * @return Collection<TKey,TValue>
 */
function collect(iterable $elements = []): Collection
{
    return Collection\collect($elements);
}

/**
 * @template TValue
 * @template TNewValue
 * @param Option<TValue>|Collection<array-key,TValue>|iterable<array-key,TValue>|TValue $value
 * @param callable(TValue=,array-key):TNewValue $fn
 * @return Option<TNewValue>|Collection<array-key,TNewValue>
 */
function map(mixed $value, callable $fn): Option|Collection
{
    return match (true) {
        $value instanceof Option => $value->map($fn),
        $value instanceof Collection => $value->map($fn),
        \is_iterable($value) => map(collect($value), $fn),
        default => map(optional($value), $fn),
    };
}

/**
 * @template TValue
 * @param Option<TValue>|Collection<array-key,TValue>|iterable<array-key,TValue>|TValue $value
 * @param callable(TValue=,array-key):bool $fn
 * @return Option<TValue>|Collection<array-key,TValue>
 */
function filter(mixed $value, callable $fn): Option|Collection
{
    return match (true) {
        $value instanceof Option => $value->filter($fn),
        $value instanceof Collection => $value->filter($fn),
        \is_iterable($value) => filter(collect($value), $fn),
        default => filter(optional($value), $fn),
    };
}
