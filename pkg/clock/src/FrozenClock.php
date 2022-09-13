<?php

declare(strict_types=1);

namespace Warp\Clock;

final class FrozenClock implements ClockInterface
{
    private ?DateTimeImmutableValue $now = null;

    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function reset(): void
    {
        $this->now = null;
    }

    public function now(): DateTimeImmutableValue
    {
        return $this->now ??= $this->clock->now();
    }
}
