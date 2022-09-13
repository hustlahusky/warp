<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin\BelongsToLink;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Warp\Bridge\Cycle\Mapper\Plugin\QueueAfterEvent;

final class BelongsToLinkPlugin implements EventSubscriberInterface
{
    public function __construct(
        private readonly BelongsToLinkHandler $handler,
    ) {
    }

    public function handle(QueueAfterEvent $event): void
    {
        $this->handler->handle($event);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QueueAfterEvent::class => 'handle',
        ];
    }
}
