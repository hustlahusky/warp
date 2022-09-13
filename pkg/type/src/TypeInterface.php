<?php

declare(strict_types=1);

namespace Warp\Type;

interface TypeInterface extends \Stringable
{
    /**
     * Print type as a string
     */
    public function __toString(): string;

    /**
     * Check that type of given value satisfies constraints
     */
    public function check(mixed $value): bool;
}
