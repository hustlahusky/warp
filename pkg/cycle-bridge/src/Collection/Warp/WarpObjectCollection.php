<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Collection\Warp;

use Warp\Bridge\Cycle\Collection\ObjectCollectionInterface;
use Warp\Collection\AbstractCollectionDecorator;
use Warp\Collection\Collection;
use Warp\Collection\CollectionInterface;
use Warp\Common\Factory\StaticConstructorInterface;
use Warp\Type\TypeInterface;

/**
 * @template V of object
 * @template P
 * @extends AbstractCollectionDecorator<V>
 * @implements ObjectCollectionInterface<V,P>
 */
final class WarpObjectCollection extends AbstractCollectionDecorator implements ObjectCollectionInterface, StaticConstructorInterface
{
    /**
     * @param ObjectIterator<V,P> $storage
     */
    private function __construct(
        private readonly ObjectIterator $storage,
        private readonly TypeInterface|null $valueType = null,
    ) {
    }

    /**
     * @template T of object
     * @param iterable<T> $elements
     * @return self<T,mixed>
     */
    public static function new(iterable $elements = [], TypeInterface|null $valueType = null): self
    {
        return new self(new ObjectIterator($elements), $valueType);
    }

    public function hasPivot(object $element): bool
    {
        return $this->storage->hasPivot($element);
    }

    public function getPivot(object $element)
    {
        return $this->storage->getPivot($element);
    }

    public function setPivot(object $element, mixed $pivot): void
    {
        $this->storage->setPivot($element, $pivot);
    }

    public function getPivotContext(): \SplObjectStorage
    {
        return $this->storage->getPivotContext();
    }

    protected function getCollection(): CollectionInterface
    {
        return Collection::new($this->storage, $this->valueType);
    }
}
