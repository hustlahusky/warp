<?php

declare(strict_types=1);

namespace Warp\Collection\Operation;

use Warp\Collection\AlterValueTypeOperationInterface;
use Warp\Common\Field\FieldInterface;

/**
 * @template K of array-key
 * @template V
 * @implements AlterValueTypeOperationInterface<K,V,int,string>
 */
final class ImplodeOperation implements AlterValueTypeOperationInterface
{
    public function __construct(
        private readonly string $glue = '',
        private readonly FieldInterface|null $field = null
    ) {
    }

    /**
     * @param \Traversable<K,V> $iterator
     * @return \Generator<int,string>
     */
    public function apply(\Traversable $iterator): \Generator
    {
        return yield from (new ReduceOperation($this->getCallback(), ''))->apply($iterator);
    }

    private function getCallback(): callable
    {
        $i = -1;

        return function ($accum, $element) use (&$i) {
            $value = null !== $this->field ? $this->field->extract($element) : $element;

            if (0 === ++$i) {
                return '' . $value;
            }

            return $accum . $this->glue . $value;
        };
    }
}
