<?php

declare(strict_types=1);

namespace Warp\Bridge\LaminasHydrator\Strategy;

use Laminas\Hydrator\Strategy\DefaultStrategy;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Warp\Clock\ClockInterface;
use Warp\DataSource\Blame\Blame;
use Warp\DataSource\Blame\BlameImmutableInterface;

/**
 * @template T of object
 */
final class BlameStrategy implements StrategyInterface
{
    /**
     * @param class-string<T>|null $actorClass
     * @param string[] $fields
     */
    public function __construct(
        private readonly ?string $actorClass = null,
        private readonly array $fields = [],
        private readonly StrategyInterface $dateStrategy = new DefaultStrategy(),
        private readonly ?ClockInterface $clock = null,
    ) {
    }

    public function extract(mixed $value, ?object $object = null)
    {
        if (!$value instanceof BlameImmutableInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Unable to extract. Expected instance of %s. Got: %s.',
                BlameImmutableInterface::class,
                \get_debug_type($value),
            ));
        }

        $output = $value->toArray($this->fields);

        if (isset($output['createdAt'])) {
            $output['createdAt'] = $this->dateStrategy->extract($output['createdAt']);
        }

        if (isset($output['updatedAt'])) {
            $output['updatedAt'] = $this->dateStrategy->extract($output['updatedAt']);
        }

        return $output;
    }

    /**
     * @param mixed $value
     * @param array<array-key,mixed>|null $data
     * @return BlameImmutableInterface<T|object>
     */
    public function hydrate(mixed $value, ?array $data = null): BlameImmutableInterface
    {
        if ($value instanceof BlameImmutableInterface) {
            return $value;
        }

        if (!\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf(
                'Expected value to be an array. Got: %s.',
                \get_debug_type($value),
            ));
        }

        if (isset($value['createdAt'])) {
            $value['createdAt'] = $this->dateStrategy->hydrate($value['createdAt'], null);
        }

        if (isset($value['updatedAt'])) {
            $value['updatedAt'] = $this->dateStrategy->hydrate($value['updatedAt'], null);
        }

        return Blame::fromArray($value, $this->actorClass, $this->clock);
    }
}
