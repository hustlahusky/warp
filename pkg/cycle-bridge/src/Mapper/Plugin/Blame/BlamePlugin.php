<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin\Blame;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Warp\Bridge\Cycle\Mapper\Plugin\QueueBeforeEvent;

/**
 * @template T of object
 */
final class BlamePlugin implements EventSubscriberInterface
{
    /**
     * @param BlameHandler<T> $handler
     */
    public function __construct(
        private readonly BlameHandler $handler,
    ) {
    }

    public function handle(QueueBeforeEvent $event): void
    {
        $this->handler->handle($event);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QueueBeforeEvent::class => 'handle',
        ];
    }
}
