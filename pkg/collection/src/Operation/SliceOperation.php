<?php

declare(strict_types=1);

namespace Warp\Collection\Operation;

/**
 * @template K of array-key
 * @template V
 * @extends AbstractOperation<K,V,K,V>
 */
final class SliceOperation extends AbstractOperation
{
    /**
     * @var positive-int|0
     */
    private readonly int $offset;

    /**
     * @var positive-int|null
     */
    private readonly int|null $limit;

    public function __construct(int $offset, int|null $limit, bool $preserveKeys = false)
    {
        parent::__construct($preserveKeys);

        if (0 > $offset) {
            throw new \InvalidArgumentException(\sprintf(
                'Expected $offset to be greater than or equal to 0. Got: %d.',
                $offset
            ));
        }

        if (null !== $limit && 1 > $limit) {
            throw new \InvalidArgumentException(\sprintf('Expected $limit to be positive. Got: %d.', $limit));
        }

        $this->offset = $offset;
        $this->limit = $limit;
    }

    protected function generator(\Traversable $iterator): \Generator
    {
        $offset = $this->offset;
        $limit = $this->limit;

        foreach ($iterator as $index => $value) {
            if (0 < $offset) {
                $offset--;
                continue;
            }

            yield $index => $value;

            if (null === $limit) {
                continue;
            }

            $limit--;

            if (0 === $limit) {
                break;
            }
        }
    }
}
