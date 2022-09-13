<?php

declare(strict_types=1);

namespace Warp\Type\Cast;

interface CasterInterface
{
    public function accepts(mixed $value): bool;

    public function cast(mixed $value): mixed;
}
