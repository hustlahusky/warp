<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin;

use Psr\EventDispatcher\EventDispatcherInterface;
use Warp\Bridge\Cycle\Mapper\MapperPluginInterface;

final class DispatcherMapperPlugin implements MapperPluginInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(object $event): object
    {
        /** @phpstan-var T $e */
        $e = $this->dispatcher->dispatch($event);
        \assert($e instanceof $event);
        return $e;
    }
}
