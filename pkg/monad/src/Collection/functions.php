<?php

declare(strict_types=1);

namespace Warp\Monad\Collection;

use Warp\Monad\Collection;

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param bool $preserveKeys
 * @return array<TKey,TValue>|list<TValue>
 * @phpstan-return ($preserveKeys is true ? array<TKey,TValue> : list<TValue>)
 */
function toArray(iterable $iter, bool $preserveKeys = true): array
{
    if (\is_array($iter)) {
        return $preserveKeys ? $iter : \array_values($iter);
    }

    return \iterator_to_array($iter, $preserveKeys);
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @return \Iterator<TKey,TValue>
 */
function toIterator(iterable $iter): \Iterator
{
    return match (true) {
        $iter instanceof \Iterator => $iter,
        \is_array($iter) => new \ArrayIterator($iter),
        default => new \IteratorIterator($iter),
    };
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @return Collection<TKey,TValue>
 */
function collect(iterable $iter = []): Collection
{
    return Collection::new($iter);
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param bool $preserveKeys
 * @return array<TKey,TValue>|list<TValue>
 * @phpstan-return ($preserveKeys is true ? array<TKey,TValue> : list<TValue>)
 */
function reverse(iterable $iter, bool $preserveKeys = false): array
{
    return \array_reverse(toArray($iter), $preserveKeys);
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param iterable<TKey,TValue> $other
 * @param iterable<TKey,TValue> ...$others
 * @return \AppendIterator<TKey,TValue,\Iterator<TKey,TValue>>
 */
function merge(iterable $iter, iterable $other, iterable ...$others): \AppendIterator
{
    /** @phpstan-var \AppendIterator<TKey,TValue,\Iterator<TKey,TValue>> $out */
    $out = new \AppendIterator();
    $out->append(toIterator($iter));
    $out->append(toIterator($other));
    foreach ($others as $another) {
        $out->append(toIterator($another));
    }

    return $out;
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param bool $preserveKeys
 * @param int $flags
 * @return array<TKey,TValue>|list<TValue>
 * @phpstan-return ($preserveKeys is true ? array<TKey,TValue> : list<TValue>)
 */
function unique(iterable $iter, bool $preserveKeys = true, int $flags = \SORT_STRING): array
{
    return \array_unique(toArray($iter, $preserveKeys), $flags);
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param int $direction
 * @phpstan-param 3|4 $direction \SORT_DESC|\SORT_ASC
 * @param callable(TValue):mixed|null $field
 * @param bool $preserveKeys
 * @return array<TKey,TValue>|array<int,TValue>
 * @phpstan-return ($preserveKeys is true ? array<TKey,TValue> : array<int,TValue>)
 */
function sort(iterable $iter, int $direction = \SORT_ASC, ?callable $field = null, bool $preserveKeys = true): array
{
    $array = toArray($iter, $preserveKeys);

    \uasort($array, static function ($left, $right) use ($field, $direction) {
        $lValue = null === $field ? $left : $field($left);
        $rValue = null === $field ? $right : $field($right);

        return ($lValue <=> $rValue) * (\SORT_DESC === $direction ? -1 : 1);
    });

    return $array;
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param int $offset
 * @param int|null $limit
 * @param bool $preserveKeys
 * @return Collection<TKey,TValue>
 * @phpstan-return ($preserveKeys is true ? Collection<TKey,TValue> : Collection<int,TValue>)
 */
function slice(iterable $iter, int $offset, int|null $limit, bool $preserveKeys = false): Collection
{
    $generator = (static function () use ($iter, $offset, $limit, $preserveKeys) {
        foreach ($iter as $index => $value) {
            if (0 < $offset) {
                $offset--;
                continue;
            }

            if ($preserveKeys) {
                yield $index => $value;
            } else {
                yield $value;
            }

            if (null === $limit) {
                continue;
            }

            $limit--;

            if (0 === $limit) {
                break;
            }
        }
    })();

    return collect($generator);
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param callable(TValue,TKey):bool|null $predicate
 * @return TValue|null
 */
function first(iterable $iter, ?callable $predicate = null): mixed
{
    foreach ($iter as $offset => $value) {
        if (null === $predicate || ($predicate)($value, $offset)) {
            return $value;
        }
    }

    return null;
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param callable(TValue,TKey):bool|null $predicate
 * @return TValue|null
 */
function last(iterable $iter, ?callable $predicate = null): mixed
{
    $out = null;

    foreach ($iter as $offset => $value) {
        if (null === $predicate || ($predicate)($value, $offset)) {
            $out = $value;
        }
    }

    return $out;
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 * @param TValue $element
 * @return bool
 */
function contains(iterable $iter, mixed $element): bool
{
    return null !== first($iter, static fn ($v) => $v === $element);
}

/**
 * @template TKey
 * @template TValue
 * @template T
 * @param iterable<TKey,TValue> $iter
 * @param callable(T,TValue):T $callback
 * @param T $initialValue
 * @return T
 */
function reduce(iterable $iter, callable $callback, mixed $initialValue = null): mixed
{
    $output = $initialValue;

    foreach ($iter as $value) {
        $output = ($callback)($output, $value);
    }

    return $output;
}

/**
 * @template TKey
 * @template TValue of \Stringable|scalar|null
 * @param iterable<TKey,TValue> $iter
 */
function implode(iterable $iter, string $glue = ''): string
{
    return reduce(
        $iter,
        static fn ($accum, $element) => $accum . ('' === $accum ? '' : $glue) . $element,
        '',
    );
}

/**
 * @template TKey
 * @template TValue of numeric
 * @param iterable<TKey,TValue> $iter
 */
function sum(iterable $iter): int|float
{
    return reduce(
        $iter,
        static fn ($accum, $element) => $accum + $element,
        0,
    );
}

/**
 * @template TKey
 * @template TValue of numeric
 * @param iterable<TKey,TValue> $iter
 */
function average(iterable $iter): int|float|null
{
    $count = 0;
    $sum = reduce(
        $iter,
        static function ($accum, $element) use (&$count) {
            ++$count;
            return $accum + $element;
        },
        0,
    );

    if (0 === $count) {
        return null;
    }

    return $sum / $count;
}

/**
 * @template TKey
 * @template TValue of numeric
 * @param iterable<TKey,TValue> $iter
 */
function median(iterable $iter): int|float|null
{
    $array = toArray($iter, false);
    if ([] === $array) {
        return null;
    }

    \usort($array, static fn ($left, $right) => $left <=> $right);

    $count = \count($array);
    $middleIndex = (int)\floor(($count - 1) / 2);

    if ($count % 2) {
        return 0 + $array[$middleIndex];
    }

    return ($array[$middleIndex] + $array[$middleIndex + 1]) / 2;
}

/**
 * @template TKey
 * @template TValue of numeric
 * @param iterable<TKey,TValue> $iter
 */
function max(iterable $iter): int|float|null
{
    return reduce($iter, static fn ($accum, $element) => \max($element, $accum));
}

/**
 * @template TKey
 * @template TValue of numeric
 * @param iterable<TKey,TValue> $iter
 */
function min(iterable $iter): int|float|null
{
    return reduce($iter, static fn ($accum, $element) => \min($element, $accum));
}

/**
 * @template TKey
 * @template TValue
 * @param iterable<TKey,TValue> $iter
 */
function count(iterable $iter): int
{
    return match (true) {
        \is_array($iter), $iter instanceof \Countable => \count($iter),
        default => \iterator_count(toIterator($iter)),
    };
}
