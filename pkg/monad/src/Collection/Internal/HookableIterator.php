<?php

declare(strict_types=1);

namespace Warp\Monad\Collection\Internal;

/**
 * @internal
 * @template TKey
 * @template TValue
 * @implements \Iterator<TKey,TValue>
 */
final class HookableIterator implements \Iterator
{
    private int $pos = 0;

    /**
     * @param \Iterator<TKey,TValue> $iterator
     * @param \WeakMap<object,callable(int,TKey,TValue):void> $hooks
     */
    private function __construct(
        private readonly \Iterator $iterator,
        private \WeakMap $hooks = new \WeakMap(),
    ) {
    }

    /**
     * @param \Iterator<TKey,TValue> $iterator
     * @param object $subscriber
     * @param callable(int,mixed,mixed):void $onYield
     * @return self<TKey,TValue>
     */
    public static function new(\Iterator $iterator, object $subscriber, callable $onYield): self
    {
        if (!$iterator instanceof self) {
            $iterator = new self($iterator);
        }

        $iterator->hooks[$subscriber] = $onYield;

        return $iterator;
    }

    public function rewind(): void
    {
        $this->pos = 0;
        $this->iterator->rewind();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function current(): mixed
    {
        $this->onYield();
        return $this->iterator->current();
    }

    public function key(): mixed
    {
        $this->onYield();
        return $this->iterator->key();
    }

    public function next(): void
    {
        ++$this->pos;
        $this->iterator->next();
        $this->onYield();
    }

    private function onYield(): void
    {
        if (!$this->iterator->valid()) {
            return;
        }

        foreach ($this->hooks as $hook) {
            $hook($this->pos, $this->iterator->key(), $this->iterator->current());
        }
    }
}
