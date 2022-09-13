<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin;

use Cycle\ORM\Heap\Node;
use Cycle\ORM\Heap\State;
use Symfony\Contracts\EventDispatcher\Event;

final class QueueBeforeEvent extends Event
{
    public function __construct(
        private readonly object $entity,
        private readonly Node $node,
        private readonly State $state,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function getState(): State
    {
        return $this->state;
    }
}
