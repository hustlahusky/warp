<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

final class BooleanStrategy implements StrategyInterface
{
    /**
     * @var array<int|string>
     */
    private readonly array $trueValue;

    /**
     * @var array<int|string>
     */
    private readonly array $falseValue;

    /**
     * @param int|string|array<int|string> $trueValue
     * @param int|string|array<int|string> $falseValue
     */
    public function __construct(
        mixed $trueValue,
        mixed $falseValue,
        private readonly bool $strict = true
    ) {
        $this->trueValue = self::prepareInputValue($trueValue, '$trueValue');
        $this->falseValue = self::prepareInputValue($falseValue, '$falseValue');
    }

    public function extract(mixed $value, ?object $object = null)
    {
        if (!\is_bool($value)) {
            throw new \InvalidArgumentException(\sprintf(
                'Unable to extract. Expected a boolean. Got: %s.',
                \get_debug_type($value)
            ));
        }

        return true === $value ? $this->trueValue[0] : $this->falseValue[0];
    }

    /**
     * @inheritDoc
     * @param array<string,mixed>|null $data
     */
    public function hydrate(mixed $value, ?array $data = null): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        foreach ($this->trueValue as $trueValue) {
            /** @noinspection TypeUnsafeComparisonInspection */
            if (($this->strict ? $value === $trueValue : $value == $trueValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int|string>
     */
    private static function prepareInputValue(mixed $inputValue, string $argument): array
    {
        $result = [];

        foreach (\is_iterable($inputValue) ? $inputValue : [$inputValue] as $value) {
            if (!\is_int($value) && !\is_string($value)) {
                throw new \InvalidArgumentException(\sprintf(
                    'Argument %s expected to be int, string or iterator of int or string. Got: %s',
                    $argument,
                    \get_debug_type($value)
                ));
            }

            $result[] = $value;
        }

        if (0 === \count($result)) {
            throw new \InvalidArgumentException(\sprintf('Argument %s cannot be empty iterable', $argument));
        }

        return $result;
    }
}
