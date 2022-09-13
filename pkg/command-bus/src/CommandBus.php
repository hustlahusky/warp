<?php

declare(strict_types=1);

namespace Warp\CommandBus;

use Warp\CommandBus\Exception\CannotInvokeHandlerException;
use Warp\CommandBus\Mapping\CommandToHandlerMappingInterface;
use Warp\Container\FactoryAggregateInterface;
use Warp\Container\FactoryContainer;
use Warp\Container\FactoryOptionsInterface;

/**
 * Receives a command and sends it through a chain of middleware for processing.
 */
class CommandBus
{
    private \Closure $middlewareChain;
    private bool $isClone = false;

    /**
     * @param array<MiddlewareInterface|class-string<MiddlewareInterface>> $middleware
     */
    public function __construct(
        private readonly CommandToHandlerMappingInterface $mapping,
        array $middleware = [],
        private readonly FactoryAggregateInterface $factory = new FactoryContainer()
    ) {
        $this->middlewareChain = $this->makeMiddlewareChain($middleware);
    }

    /**
     * Clone command bus
     */
    public function __clone()
    {
        $middlewareChain = $this->middlewareChain->bindTo($this);
        \assert($middlewareChain instanceof \Closure);
        $this->middlewareChain = $middlewareChain;
        $this->isClone = true;
    }

    /**
     * Executes the given command and optionally returns a value
     */
    public function handle(object $command): mixed
    {
        return ($this->middlewareChain)($command);
    }

    /**
     * Creates handler object by given class name using factory.
     *
     * You can modify this procedure in your successor class.
     *
     * @template T of object
     * @param class-string<T> $handlerClass
     * @param FactoryOptionsInterface|array<string,mixed>|null $options
     * @return T
     */
    protected function makeHandlerObject(
        string $handlerClass,
        array|FactoryOptionsInterface|null $options = null
    ): object {
        return $this->factory->make($handlerClass, $options);
    }

    /**
     * @param array<MiddlewareInterface|class-string<MiddlewareInterface>> $middlewareList
     */
    private function makeMiddlewareChain(array $middlewareList): \Closure
    {
        $lastCallable = fn (object $command) => $this->makeCommandHandler($command)($command);

        while ($item = \array_pop($middlewareList)) {
            try {
                $middleware = \is_string($item) ? $this->factory->make($item) : $item;

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new \InvalidArgumentException('Middleware should implement proper interface.');
                }
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException(
                    \sprintf('Invalid middleware: %s.', \is_string($item) ? $item : \get_debug_type($item)),
                    $e->getCode(),
                    $e,
                );
            }

            $lastCallable = function ($command) use ($middleware, $lastCallable) {
                $lastCallable = $this->isClone ? \Closure::bind($lastCallable, $this) : $lastCallable;
                return $middleware->execute($command, $lastCallable);
            };
        }

        return $lastCallable;
    }

    private function makeCommandHandler(object $command): callable
    {
        $commandClass = $command::class;
        $handlerClass = $this->mapping->getClassName($commandClass);
        $handlerMethod = $this->mapping->getMethodName($commandClass);

        $handler = [$this->makeHandlerObject($handlerClass), $handlerMethod];

        if (!\is_callable($handler)) {
            throw CannotInvokeHandlerException::methodNotExists($command, $handlerClass, $handlerMethod);
        }

        return $handler;
    }
}
