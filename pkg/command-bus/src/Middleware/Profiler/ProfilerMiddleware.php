<?php

declare(strict_types=1);

namespace Warp\CommandBus\Middleware\Profiler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Stopwatch\Stopwatch;
use Warp\CommandBus\MiddlewareInterface;

/**
 * @todo: get rid from logger dependency here.
 */
final class ProfilerMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    /**
     * @var callable(object):bool
     */
    private $predicate;

    /**
     * @param null|callable(object):bool $predicate
     */
    public function __construct(
        private readonly Stopwatch $stopwatch,
        ?callable $predicate = null,
        private LoggerInterface $logger = new NullLogger(),
        private readonly string $logLevel = LogLevel::DEBUG
    ) {
        $this->predicate = $predicate ?? static fn (object $message): bool => true;
    }

    public function execute(object $command, callable $next): mixed
    {
        if (!($this->predicate)($command)) {
            return $next($command);
        }

        $this->startStopwatch($command);

        try {
            return $next($command);
        } finally {
            $this->stopStopwatch($command);
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function resolveCommandProfilingEventName(object $command): string
    {
        $eventName = $command instanceof MayBeProfiledMessageInterface ? $command->getProfilingEventName() : null;
        return $eventName ?? $command::class;
    }

    private function startStopwatch(object $command): void
    {
        $eventName = $this->resolveCommandProfilingEventName($command);

        $eventCategory = $command instanceof MayBeProfiledMessageInterface ? $command->getProfilingCategory() : null;

        $event = $this->stopwatch->start($eventName, $eventCategory);

        $this->logger->log($this->logLevel, \sprintf('Profiling event %s started', $eventName), [
            'event' => $event,
        ]);
    }

    private function stopStopwatch(object $command): void
    {
        $eventName = $this->resolveCommandProfilingEventName($command);

        $profilingData = $this->stopwatch->stop($eventName);

        $this->logger->log(
            $this->logLevel,
            \sprintf('Profiling event %s finished (%s)', $eventName, $profilingData),
            [
                'event' => $profilingData,
            ]
        );
    }
}
