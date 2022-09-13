<?php

declare(strict_types=1);

namespace Warp\Collection\Iterator;

use Warp\Collection\OperationInterface;

/**
 * @template K of array-key
 * @template V
 * @implements \IteratorAggregate<K,V>
 */
final class OperationIterator implements \IteratorAggregate
{
    /**
     * @param \Traversable<array-key,mixed> $iterator
     * @param OperationInterface<array-key,mixed,K,V> $operation
     */
    public function __construct(
        private readonly \Traversable $iterator,
        private readonly OperationInterface $operation,
    ) {
    }

    /**
     * @return \Generator<K,V>
     */
    public function getIterator(): \Generator
    {
        yield from $this->operation->apply($this->iterator);
    }
}
