<?php

declare(strict_types=1);

namespace Warp\Clock;

final class SystemClock implements ClockInterface
{
    public function __construct(
        private readonly \DateTimeZone $timezone,
    ) {
    }

    public static function fromUTC(): self
    {
        return new self(new \DateTimeZone('UTC'));
    }

    public static function fromSystemTimezone(): self
    {
        return new self(new \DateTimeZone(\date_default_timezone_get()));
    }

    public function now(): DateTimeImmutableValue
    {
        return DateTimeImmutableValue::now($this->timezone);
    }
}
