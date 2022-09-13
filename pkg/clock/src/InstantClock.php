<?php

declare(strict_types=1);

namespace Warp\Clock;

final class InstantClock implements ClockInterface
{
    public function __construct(
        private readonly DateTimeImmutableValue $now,
    ) {
    }

    public function now(): DateTimeImmutableValue
    {
        return $this->now;
    }
}
