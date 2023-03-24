<?php

declare(strict_types=1);

namespace Warp\Collection\Operation;

use Warp\Common\Field\FieldInterface;

/**
 * @template K of array-key
 * @template V
 * @extends AbstractOperation<K,V,K,V>
 */
final class IndexByOperation extends AbstractOperation
{
    /**
     * @var callable(V):K
     */
    private $keyExtractor;

    /**
     * @param FieldInterface|callable(V):K $keyExtractor
     */
    public function __construct(FieldInterface|callable $keyExtractor)
    {
        parent::__construct(true);

        if ($keyExtractor instanceof FieldInterface) {
            $field = $keyExtractor;
            $keyExtractor = static function ($element) use ($field): string|int {
                $offset = $field->extract($element);
                return \is_string($offset) || \is_int($offset) ? $offset : (string)$offset;
            };
        }

        // @phpstan-ignore-next-line
        $this->keyExtractor = $keyExtractor;
    }

    protected function generator(\Traversable $iterator): \Generator
    {
        /** @var V $element */
        foreach ($iterator as $element) {
            yield ($this->keyExtractor)($element) => $element;
        }
    }
}
