<?php

declare(strict_types=1);

namespace Warp\Collection;

use Warp\Collection\Iterator\ArrayIterator;
use Warp\Type\BuiltinType;
use Warp\Type\MixedType;
use Warp\Type\TypeInterface;
use Warp\Type\UnionType;

/**
 * @template K of array-key
 * @template V
 * @implements MapInterface<K,V>
 */
abstract class AbstractMap implements MapInterface
{
    private static ?TypeInterface $keyType = null;

    /**
     * @param ArrayIterator<K,V> $source
     */
    protected function __construct(
        protected ArrayIterator $source,
        protected TypeInterface $valueType
    ) {
    }

    public function set(int|string $key, mixed $element): void
    {
        $this->assertValueType($element);
        $this->source->offsetSet($key, $element);
    }

    public function unset(int|string $key): void
    {
        if (!$this->source->offsetExists($key)) {
            return;
        }

        $this->source->offsetUnset($key);
    }

    public function has(int|string $key): bool
    {
        return $this->source->offsetExists($key);
    }

    public function get(int|string $key)
    {
        if (!$this->source->offsetExists($key)) {
            return null;
        }

        return $this->source->offsetGet($key);
    }

    public function applyOperation(OperationInterface $operation): MapInterface
    {
        return $this->withSource(
            new ArrayIterator($operation->apply($this->source)),
            $operation instanceof AlterValueTypeOperationInterface ? MixedType::new() : null
        );
    }

    public function merge(iterable $other, iterable ...$others): MapInterface
    {
        return $this->applyOperation(new Operation\MergeOperation([$other, ...$others], true));
    }

    public function values(): CollectionInterface
    {
        return $this->makeCollection((new Operation\ValuesOperation())->apply($this->source), $this->valueType);
    }

    public function keys(): CollectionInterface
    {
        return $this->makeCollection((new Operation\KeysOperation())->apply($this->source), self::getKeyType());
    }

    public function firstKey()
    {
        return \array_key_first($this->source->getArrayCopy());
    }

    public function lastKey()
    {
        return \array_key_last($this->source->getArrayCopy());
    }

    public function getIterator(): \Traversable
    {
        return $this->source;
    }

    public function count(): int
    {
        return $this->source->count();
    }

    /**
     * @return array<K,V>
     */
    public function jsonSerialize(): array
    {
        return $this->source->getArrayCopy();
    }

    /**
     * @template T
     * @param ArrayIterator<K,T> $source
     * @return static<K,T>
     */
    abstract protected function withSource(ArrayIterator $source, ?TypeInterface $valueType = null): MapInterface;

    /**
     * @template T
     * @param iterable<int,T> $elements
     * @return CollectionInterface<T>
     */
    abstract protected function makeCollection(
        iterable $elements = [],
        ?TypeInterface $valueType = null
    ): CollectionInterface;

    /**
     * @param V ...$values
     */
    protected function assertValueType(...$values): void
    {
        foreach ($values as $value) {
            if ($this->valueType->check($value)) {
                continue;
            }

            throw new \LogicException(\sprintf(
                'Map accepts only elements of type %s. Got: %s.',
                $this->valueType,
                \get_debug_type($value)
            ));
        }
    }

    /**
     * @return TypeInterface array-key type checker
     */
    final protected static function getKeyType(): TypeInterface
    {
        return self::$keyType ??= UnionType::new(
            BuiltinType::new(BuiltinType::INT),
            BuiltinType::new(BuiltinType::STRING),
        );
    }
}
