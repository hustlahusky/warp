<?php

declare(strict_types=1);

namespace Warp\Type\Fixtures;

final class InvalidAggregatedType extends \Warp\Type\AbstractAggregatedType
{
    public const DELIMITER = '';

    public function check(mixed $value): bool
    {
        return false;
    }
}
