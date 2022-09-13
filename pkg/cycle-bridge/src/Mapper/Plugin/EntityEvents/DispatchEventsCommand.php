<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin\EntityEvents;

use Psr\EventDispatcher\EventDispatcherInterface;
use Warp\Bridge\Cycle\Mapper\AbstractCommand;

final class DispatchEventsCommand extends AbstractCommand
{
    /**
     * @param object[] $events
     */
    public function __construct(
        private readonly array $events,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function isReady(): bool
    {
        return true;
    }

    public function complete(): void
    {
        foreach ($this->events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
