<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin;

use Symfony\Contracts\EventDispatcher\Event;

final class ExtractBeforeEvent extends Event
{
    public function __construct(
        private readonly object $entity,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
