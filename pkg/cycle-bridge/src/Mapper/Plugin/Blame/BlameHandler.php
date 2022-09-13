<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin\Blame;

use Warp\Bridge\Cycle\Mapper\Plugin\QueueBeforeEvent;
use Warp\DataSource\Blame\BlamableInterface;
use Warp\DataSource\Blame\BlameActorProviderInterface;

/**
 * @template T of object
 */
final class BlameHandler
{
    /**
     * @param BlameActorProviderInterface<T> $actorProvider
     */
    public function __construct(
        private readonly BlameActorProviderInterface $actorProvider,
        private readonly bool $force = false,
    ) {
    }

    public function handle(QueueBeforeEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof BlamableInterface) {
            return;
        }

        $entity->blame($this->actorProvider->getActor(), $this->force);
    }
}
