<?php

declare(strict_types=1);

namespace Warp\Monad;

use Warp\Monad\Collection\Internal\CollectionItem;
use Warp\Monad\Collection\Internal\HookableIterator;
use function Warp\Monad\Collection\toIterator;

/**
 * @template TKey
 * @template TValue
 * @implements \Iterator<TKey,TValue>
 */
final class Collection implements \Iterator, \Countable
{
    private int $pos = 0;

    /**
     * @var array<int,CollectionItem<TKey,TValue>|null>
     */
    private array $items = [];

    /**
     * @var \Iterator<mixed,mixed>|null
     */
    private \Iterator|null $iterator = null;

    /**
     * @var MapperChain<CollectionItem<mixed,mixed>,CollectionItem<TKey,TValue>,null>
     */
    private MapperChain $mapper;

    /**
     * @var \Closure(CollectionItem<mixed,mixed>):(CollectionItem<TKey,TValue>|null)|null
     */
    private \Closure|null $mapperFn = null;

    private function __construct()
    {
        $this->mapper = new MapperChain();
    }

    public function __destruct()
    {
        $this->pos = 0;
        $this->items = [];
        $this->iterator = null;
        $this->mapper = new MapperChain();
        $this->mapperFn = null;
    }

    /**
     * @param iterable<TKey,TValue> $elements
     * @return self<TKey,TValue>
     */
    public static function new(iterable $elements = []): self
    {
        if ($elements instanceof self) {
            return $elements;
        }

        $out = new self();
        $out->iterator = toIterator($elements);

        return $out;
    }

    /**
     * @template TNewValue
     * @param callable(TValue,TKey):TNewValue $fn
     * @return self<TKey,TNewValue>
     */
    public function map(callable $fn): self
    {
        return $this->withMapper(
            $this->mapper->map(static fn (CollectionItem $v) => new CollectionItem($v->key, $fn($v->value, $v->key))),
        );
    }

    /**
     * @template TNewKey
     * @param callable(TValue,TKey):TNewKey $fn
     * @return self<TNewKey,TValue>
     */
    public function indexBy(callable $fn): self
    {
        return $this->withMapper(
            $this->mapper->map(static fn (CollectionItem $v) => new CollectionItem($fn($v->value, $v->key), $v->value)),
        );
    }

    /**
     * @param callable(TValue,TKey):bool $fn
     * @return self<TKey,TValue>
     */
    public function filter(callable $fn): self
    {
        return $this->withMapper($this->mapper->filter(static fn (CollectionItem $v) => $fn($v->value, $v->key)));
    }

    public function rewind(): void
    {
        $this->pos = 0;
    }

    public function valid(): bool
    {
        return null !== $this->getCurrentItem();
    }

    /**
     * @return TValue|null
     */
    public function current(): mixed
    {
        return $this->getCurrentItem()?->value;
    }

    /**
     * @return TKey|null
     */
    #[\ReturnTypeWillChange]
    public function key(): mixed
    {
        return $this->getCurrentItem()?->key;
    }

    public function next(): void
    {
        ++$this->pos;
    }

    public function count(): int
    {
        $pos = $this->pos;
        $count = \iterator_count($this);
        $this->pos = $pos;
        return $count;
    }

    /**
     * @return CollectionItem<TKey,TValue>|null
     */
    private function getCurrentItem(): CollectionItem|null
    {
        $cap = \count($this->items);
        while ($this->pos < $cap) {
            if (null !== $this->items[$this->pos]) {
                break;
            }

            ++$this->pos;
        }

        $this->pullItem();

        $item = $this->items[$this->pos] ?? null;
        if (null === $item) {
            return null === $this->iterator ? null : $this->getCurrentItem();
        }

        return $item;
    }

    private function pullItem(): void
    {
        if (null === $this->iterator) {
            return;
        }

        if (isset($this->items[$this->pos])) {
            return;
        }

        if (!$this->iterator->valid()) {
            $this->iterator = null;
            return;
        }

        $item = new CollectionItem($this->iterator->key(), $this->iterator->current());
        $this->iterator->next();
        $this->onYield($this->pos, $item);
    }

    /**
     * @param CollectionItem<TKey,TValue> $item
     */
    private function onYield(int $pos, CollectionItem $item): void
    {
        if (isset($this->items[$pos])) {
            return;
        }

        if (null === $this->mapperFn) {
            $this->mapperFn = $this->mapper->compile();
            $this->mapper = new MapperChain();
        }

        $this->items[$pos] = ($this->mapperFn)($item);
    }

    /**
     * @template TNewKey
     * @template TNewValue
     * @param MapperChain<CollectionItem<mixed,mixed>,CollectionItem<TNewKey,TNewValue>,null> $mapper
     * @return self<TNewKey,TNewValue>
     */
    private function withMapper(MapperChain $mapper): self
    {
        if (null !== $this->mapperFn) {
            $pos = $this->pos;
            while ($this->valid()) {
                $this->next();
            }
            $this->pos = $pos;
        }

        /** @var self<TNewKey,TNewValue> $copy */
        $copy = new self();
        if (null !== $this->iterator) {
            $ref = \WeakReference::create($this);
            $copy->iterator = HookableIterator::new(
                $this->iterator,
                $this,
                static function (int $pos, mixed $key, mixed $value) use ($ref): void {
                    /** @var self<mixed,mixed>|null $collection */
                    $collection = $ref->get();
                    $collection?->onYield($pos, new CollectionItem($key, $value));
                },
            );
        }
        $copy->mapper = $mapper;

        foreach ($this->items as $item) {
            if (null === $item) {
                continue;
            }

            if (null === $copy->mapperFn) {
                $copy->mapperFn = $copy->mapper->compile();
                $copy->mapper = new MapperChain();
            }

            $item = ($copy->mapperFn)($item);
            if (null === $item) {
                continue;
            }

            $copy->items[] = $item;
        }

        return $copy;
    }
}
