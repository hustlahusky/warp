<?php

declare(strict_types=1);

namespace Warp\Collection;

/**
 * @template V
 */
interface MutableInterface
{
    /**
     * Clear collection.
     */
    public function clear(): void;

    /**
     * Add element(s) to collection.
     * @param V $element
     * @param V ...$elements
     */
    public function add(mixed $element, mixed ...$elements): void;

    /**
     * Remove element(s) from collection.
     * @param V $element
     * @param V ...$elements
     */
    public function remove(mixed $element, mixed ...$elements): void;

    /**
     * Replace one element with another.
     * @param V $element
     * @param V $replacement
     */
    public function replace(mixed $element, mixed $replacement): void;
}
