<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Collection;

/**
 * @template T of object
 * @template P
 */
final class Change
{
    public const ADD = 'ADD';
    public const REMOVE = 'REMOVE';

    /**
     * @phpstan-param self::ADD|self::REMOVE $type
     * @param T $element
     * @param P|null $pivot
     */
    private function __construct(
        private readonly string $type,
        private readonly object $element,
        private mixed $pivot = null,
    ) {
    }

    /**
     * @phpstan-return self::ADD|self::REMOVE
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return T
     */
    public function getElement(): object
    {
        return $this->element;
    }

    /**
     * @return P|null
     */
    public function getPivot(): mixed
    {
        return $this->pivot;
    }

    /**
     * @param P|null $pivot
     */
    public function setPivot(mixed $pivot): void
    {
        $this->pivot = $pivot;
    }

    /**
     * @param T $element
     * @param P|null $pivot
     * @return self<T,P>
     */
    public static function add(object $element, mixed $pivot = null): self
    {
        return new self(self::ADD, $element, $pivot);
    }

    /**
     * @param T $element
     * @param P|null $pivot
     * @return self<T,P>
     */
    public static function remove(object $element, mixed $pivot = null): self
    {
        return new self(self::REMOVE, $element, $pivot);
    }

    /**
     * @param T $element
     * @param T ...$elements
     * @return \Generator<self<T,mixed>>
     */
    public static function addElements(object $element, object ...$elements): \Generator
    {
        foreach ([$element, ...$elements] as $item) {
            yield self::add($item);
        }
    }

    /**
     * @param T $element
     * @param T ...$elements
     * @return \Generator<self<T,mixed>>
     */
    public static function removeElements(object $element, object ...$elements): \Generator
    {
        foreach ([$element, ...$elements] as $item) {
            yield self::remove($item);
        }
    }
}
