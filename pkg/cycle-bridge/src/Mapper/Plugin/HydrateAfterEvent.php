<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin;

use Symfony\Contracts\EventDispatcher\Event;

final class HydrateAfterEvent extends Event
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(
        private readonly object $entity,
        private readonly array $data,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
