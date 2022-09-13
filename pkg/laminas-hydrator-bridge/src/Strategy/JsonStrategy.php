<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

final class JsonStrategy implements StrategyInterface
{
    private readonly int $decodeFlags;
    private readonly int $encodeFlags;

    /**
     * @param positive-int $depth
     */
    public function __construct(
        private readonly bool $associative = true,
        int $decodeFlags = 0,
        int $encodeFlags = 0,
        private readonly int $depth = 512,
    ) {
        $this->decodeFlags = $decodeFlags | \JSON_THROW_ON_ERROR;
        $this->encodeFlags = ($encodeFlags & ~\JSON_PARTIAL_OUTPUT_ON_ERROR) | \JSON_THROW_ON_ERROR;
    }

    public function extract(mixed $value, ?object $object = null): string
    {
        $json = \json_encode($value, $this->encodeFlags, $this->depth);
        \assert(false !== $json);
        return $json;
    }

    /**
     * @param array<string,mixed>|null $data
     */
    public function hydrate(mixed $value, ?array $data = null): mixed
    {
        return \json_decode((string)$value, $this->associative, $this->depth, $this->decodeFlags);
    }
}
