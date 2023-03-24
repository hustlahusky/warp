<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use Warp\Clock\DateTimeImmutableValue;
use Warp\Clock\DateTimeValue;
use Warp\Clock\DateTimeValueInterface;

/**
 * @template T of DateTimeValue|DateTimeImmutableValue
 */
final class DateValueStrategy implements StrategyInterface
{
    /**
     * @var class-string<T>
     */
    private readonly string $dateClass;
    private readonly \DateTimeZone $timezone;
    private readonly string $timezoneOffset;

    /**
     * @param class-string<T> $dateClass
     */
    public function __construct(
        private readonly string $format,
        string $dateClass = DateTimeImmutableValue::class,
        \DateTimeZone|string|null $timezone = null
    ) {
        if (!\is_subclass_of($dateClass, DateTimeValueInterface::class)) {
            throw new \InvalidArgumentException(\sprintf(
                'Argument #2 ($dateClass) expected to be subclass of %s. Got: %s.',
                DateTimeValueInterface::class,
                $dateClass,
            ));
        }

        $this->dateClass = $dateClass;
        $this->timezone = $timezone instanceof \DateTimeZone
            ? $timezone
            : new \DateTimeZone($timezone ?: \date_default_timezone_get());
        $this->timezoneOffset = (new \DateTimeImmutable('now', $this->timezone))->format('Z');
    }

    /**
     * @inheritDoc
     * @param T $value
     */
    public function extract(mixed $value, ?object $object = null): string
    {
        if (!$value instanceof DateTimeValueInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Unable to extract. Expected instance of %s. Got: %s.',
                DateTimeValueInterface::class,
                \get_debug_type($value),
            ));
        }

        return $this->makeDateValueObject($value)->format($this->format);
    }

    /**
     * @inheritDoc
     * @param array<string,mixed>|null $data
     * @return T
     */
    public function hydrate(mixed $value, ?array $data = null)
    {
        if ($value instanceof \DateTimeInterface) {
            return $this->makeDateValueObject($value);
        }

        $date = \DateTimeImmutable::createFromFormat($this->format, (string)$value, $this->timezone);
        // @phpstan-ignore-next-line
        return $date ? $this->dateClass::from($date) : $this->makeDateValueObject($value);
    }

    /**
     * @param \DateTimeInterface|int|string $value
     * @return T
     */
    private function makeDateValueObject(mixed $value): DateTimeValueInterface
    {
        if ($value instanceof $this->dateClass && $this->timezoneOffset === $value->format('Z')) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            $date = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $value->format('Y-m-d H:i:s.u'),
                $value->getTimezone()
            );
            \assert(false !== $date);
            $date = $date->setTimezone($this->timezone);

            // @phpstan-ignore-next-line
            return $this->dateClass::from($date);
        }

        $timestamp = \filter_var($value, \FILTER_VALIDATE_INT);
        if ($timestamp) {
            return new $this->dateClass('@' . $timestamp, $this->timezone);
        }

        $value = \trim((string)$value);
        if ('' === $value) {
            throw new \InvalidArgumentException('Unable to create date from empty string.');
        }

        return new $this->dateClass($value, $this->timezone);
    }
}
