<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * @template T
 */
final class RefreshStrategy implements StrategyInterface
{
    /**
     * @param T $value
     */
    public function __construct(
        private readonly mixed $value = null,
    ) {
    }

    /**
     * @inheritDoc
     * @return T
     */
    public function extract(mixed $value, ?object $object = null)
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     * @param array<string,mixed>|null $data
     * @return T
     */
    public function hydrate(mixed $value, ?array $data = null)
    {
        return $this->value;
    }
}
