<?php

declare(strict_types=1);

namespace Warp\Collection\Operation;

use Warp\Collection\OperationInterface;

/**
 * @template IK of array-key
 * @template IV
 * @template OK of array-key
 * @template OV
 * @implements OperationInterface<IK,IV,OK,OV>
 */
abstract class AbstractOperation implements OperationInterface
{
    public function __construct(
        protected bool $preserveKeys = false,
    ) {
    }

    /**
     * @inheritDoc
     * @return \Generator<OK,OV>
     */
    final public function apply(\Traversable $iterator): \Generator
    {
        foreach ($this->generator($iterator) as $offset => $value) {
            if ($this->preserveKeys) {
                yield $offset => $value;
                continue;
            }

            yield $value;
        }
    }

    /**
     * @param \Traversable<IK,IV> $iterator
     * @return \Generator<OK,OV>
     */
    abstract protected function generator(\Traversable $iterator): \Generator;
}
