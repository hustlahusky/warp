<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

final class NullableStrategy implements StrategyInterface
{
    /**
     * @var callable(mixed):bool
     */
    private $nullValuePredicate;

    /**
     * @param null|callable(mixed):bool $nullValuePredicate
     */
    public function __construct(
        private readonly StrategyInterface $strategy,
        ?callable $nullValuePredicate = null,
    ) {
        $this->nullValuePredicate = $nullValuePredicate ?? static fn ($value) => null === $value;
    }

    /**
     * @inheritDoc
     */
    public function extract(mixed $value, ?object $object = null)
    {
        if (($this->nullValuePredicate)($value)) {
            return null;
        }

        return $this->strategy->extract($value, $object);
    }

    /**
     * @inheritDoc
     * @param array<string,mixed>|null $data
     */
    public function hydrate(mixed $value, ?array $data = null)
    {
        if (($this->nullValuePredicate)($value)) {
            return null;
        }

        return $this->strategy->hydrate($value, $data);
    }
}
