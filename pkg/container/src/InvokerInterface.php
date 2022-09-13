<?php

declare(strict_types=1);

namespace Warp\Container;

interface InvokerInterface
{
    /**
     * Invoke a callable via the container.
     * @template T
     * @param callable():T $callable
     * @param InvokerOptionsInterface|array<string,mixed>|null $options
     * @return T
     */
    public function invoke(callable $callable, array|InvokerOptionsInterface|null $options = null): mixed;
}
