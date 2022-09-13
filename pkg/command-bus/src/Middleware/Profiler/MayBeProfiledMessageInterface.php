<?php

declare(strict_types=1);

namespace Warp\CommandBus\Middleware\Profiler;

interface MayBeProfiledMessageInterface
{
    /**
     * Returns event name for profiling
     */
    public function getProfilingEventName(): ?string;

    /**
     * Returns event category for profiling
     */
    public function getProfilingCategory(): ?string;
}
