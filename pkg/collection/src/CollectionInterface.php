<?php

declare(strict_types=1);

namespace Warp\Collection;

use Warp\Common\Field\FieldInterface;
use Warp\Criteria\CriteriaInterface;
use Warp\Criteria\FilterableInterface;

/**
 * Collection interface.
 *
 * @template V
 * @extends \IteratorAggregate<array-key,V>
 * @extends MutableInterface<V>
 */
interface CollectionInterface extends MutableInterface, FilterableInterface, \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * @template T
     * @param OperationInterface<array-key,V,array-key,T> $operation
     * @return static<T>
     */
    public function applyOperation(OperationInterface $operation): self;

    /**
     * Filter collection.
     * @param callable|null $callback
     * @return static<V>
     */
    public function filter(?callable $callback = null): self;

    /**
     * Map collection.
     * @template M
     * @param callable(V,array-key):M $callback
     * @return static<M>
     */
    public function map(callable $callback): self;

    /**
     * @return static<V>
     */
    public function reverse(): self;

    /**
     * @param iterable<V> $other
     * @param iterable<V> ...$others
     * @return static<V>
     */
    public function merge(iterable $other, iterable ...$others): self;

    /**
     * @return static<V>
     */
    public function unique(): self;

    /**
     * @param FieldInterface|null $field
     * @param int $direction
     * @return static<V>
     */
    public function sort(?FieldInterface $field = null, int $direction = \SORT_ASC): self;

    /**
     * @param int $offset
     * @param int|null $limit
     * @return static<V>
     */
    public function slice(int $offset, ?int $limit = null): self;

    /**
     * @param CriteriaInterface $criteria
     * @return CollectionInterface<V>
     */
    public function matching(CriteriaInterface $criteria): self;

    /**
     * @return array<array-key,V>
     */
    public function all(): array;

    /**
     * @param callable(V,int,\Traversable<int,V>):bool $callback
     * @return V|null
     */
    public function find(callable $callback);

    /**
     * @param V $element
     * @return bool
     */
    public function contains(mixed $element): bool;

    /**
     * @return V|null
     */
    public function first();

    /**
     * @return V|null
     */
    public function last();

    /**
     * @template R
     * @param callable(R|null,V):R $callback
     * @param R|null $initialValue
     * @return R|null
     */
    public function reduce(callable $callback, mixed $initialValue = null);

    /**
     * @param string $glue
     * @param FieldInterface|null $field
     * @return string
     */
    public function implode(string $glue = '', ?FieldInterface $field = null): string;

    public function sum(?FieldInterface $field = null): int|float;

    public function average(?FieldInterface $field = null): int|float|null;

    public function median(?FieldInterface $field = null): int|float|null;

    public function max(?FieldInterface $field = null): int|float|null;

    public function min(?FieldInterface $field = null): int|float|null;

    /**
     * @param FieldInterface|callable(V):array-key $keyExtractor
     * @return MapInterface<array-key,V>
     */
    public function indexBy(FieldInterface|callable $keyExtractor): MapInterface;

    /**
     * @param FieldInterface|callable(V):array-key $keyExtractor
     * @return MapInterface<array-key,CollectionInterface<V>>
     */
    public function groupBy(FieldInterface|callable $keyExtractor): MapInterface;

    /**
     * @return \Traversable<array-key,V>
     */
    public function getIterator(): \Traversable;
}
