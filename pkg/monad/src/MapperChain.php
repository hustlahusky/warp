<?php

declare(strict_types=1);

namespace Warp\Monad;

/**
 * @template TLeft
 * @template TRight
 * @template TFilteredOut
 */
final class MapperChain implements \Countable
{
    /**
     * @var list<callable(TLeft,callable(TLeft):TRight):TRight>
     */
    private array $mappers = [];

    /**
     * @var \Closure(TLeft):(TRight|TFilteredOut)|null
     */
    private \Closure|null $closure = null;

    /**
     * @param TFilteredOut $filteredOutValue
     */
    public function __construct(
        private readonly mixed $filteredOutValue = null,
    ) {
    }

    public function __clone(): void
    {
        $this->closure = null;
    }

    public function count(): int
    {
        return \count($this->mappers);
    }

    /**
     * @template TNewRight
     * @param callable(TRight):TNewRight $fn
     * @return self<TLeft,TNewRight,TFilteredOut>
     */
    public function map(callable $fn): self
    {
        $copy = clone $this;
        $copy->mappers[] = static fn (mixed $val, callable $next) => $next($fn($val));
        return $copy;
    }

    /**
     * @param callable(TRight):bool $fn
     * @return self<TLeft,TRight,TFilteredOut>
     */
    public function filter(callable $fn): self
    {
        $copy = clone $this;
        $copy->mappers[] = static fn (mixed $val, callable $next) => $fn($val) ? $next($val) : $copy->filteredOutValue;
        return $copy;
    }

    /**
     * @return \Closure(TLeft):(TRight|TFilteredOut)
     */
    public function compile(): \Closure
    {
        if (null !== $this->closure) {
            return $this->closure;
        }

        $lastCallable = static fn (mixed $val) => $val;

        while ($mapper = \array_pop($this->mappers)) {
            $lastCallable = static fn (mixed $val) => $mapper($val, $lastCallable);
        }

        return $this->closure = $lastCallable;
    }
}
