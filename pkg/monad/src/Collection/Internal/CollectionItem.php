<?php

declare(strict_types=1);

namespace Warp\Monad\Collection\Internal;

/**
 * @internal
 * @template TKey
 * @template TValue
 */
final class CollectionItem
{
    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function __construct(
        public readonly mixed $key,
        public readonly mixed $value,
    ) {
    }
}
