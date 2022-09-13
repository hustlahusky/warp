<?php

declare(strict_types=1);

namespace Warp\Collection\Operation;

use Warp\Type\TypeInterface;

/**
 * @template K of array-key
 * @template V
 * @extends AbstractOperation<K,V,K,V>
 */
final class TypeCheckOperation extends AbstractOperation
{
    public function __construct(
        private readonly TypeInterface $valueType,
        bool $preserveKeys = false,
    ) {
        parent::__construct($preserveKeys);
    }

    protected function generator(\Traversable $iterator): \Generator
    {
        foreach ($iterator as $offset => $value) {
            if (!$this->valueType->check($value)) {
                throw new \LogicException(\sprintf(
                    'Iterator accepts only elements of type %s. Got: %s.',
                    $this->valueType,
                    \get_debug_type($value),
                ));
            }

            yield $offset => $value;
        }
    }
}
