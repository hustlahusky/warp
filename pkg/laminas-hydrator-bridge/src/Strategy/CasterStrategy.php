<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use Warp\Type\Cast\CasterInterface;

final class CasterStrategy implements StrategyInterface
{
    private readonly CasterInterface $hydrateCast;
    private readonly CasterInterface $extractCast;

    public function __construct(CasterInterface $hydrateCast, ?CasterInterface $extractCast = null)
    {
        $this->hydrateCast = $hydrateCast;
        $this->extractCast = $extractCast ?? $hydrateCast;
    }

    public function extract(mixed $value, ?object $object = null)
    {
        return $this->extractCast->cast($value);
    }

    /**
     * @inheritDoc
     * @param array<string,mixed>|null $data
     */
    public function hydrate(mixed $value, ?array $data = null)
    {
        return $this->hydrateCast->cast($value);
    }
}
