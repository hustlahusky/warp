<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle;

use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\ReferenceInterface;
use Warp\DataSource\EntityNotFoundException;
use Warp\DataSource\EntityReferenceInterface;

/**
 * @template E of object
 * @implements EntityReferenceInterface<E>
 */
final class EntityReference implements EntityReferenceInterface, PromiseInterface
{
    private bool $loaded;

    /**
     * @param class-string<E> $class
     * @param E|null $entity
     */
    private function __construct(
        private readonly string $class,
        private ?object $entity = null,
        private readonly ?ReferenceInterface $reference = null,
    ) {
        $this->loaded = null !== $this->entity;
    }

    public function __loaded(): bool
    {
        return $this->loaded;
    }

    /**
     * @return E|null
     */
    public function __resolve(): ?object
    {
        if ($this->loaded) {
            return $this->entity;
        }

        if ($this->reference instanceof PromiseInterface) {
            /** @phpstan-var E $entity */
            $entity = $this->reference->__resolve();
            $this->loaded = true;
            return $this->entity = $entity;
        }

        throw new \RuntimeException('Unable to resolve reference.');
    }

    public function __role(): string
    {
        if (null !== $this->reference) {
            return $this->reference->__role();
        }

        return $this->class;
    }

    /**
     * @return array<array-key,mixed>
     */
    public function __scope(): array
    {
        if (null !== $this->reference) {
            return $this->reference->__scope();
        }

        return [];
    }

    public function getEntity(): object
    {
        if (null === $entity = $this->getEntityOrNull()) {
            throw EntityNotFoundException::byPrimary($this->__role(), \implode(',', $this->__scope()));
        }

        return $entity;
    }

    public function getEntityOrNull(): ?object
    {
        return $this->__resolve();
    }

    public function equals(EntityReferenceInterface $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        if ($this->__loaded() && $other->__loaded()) {
            return $this->__resolve() === $other->__resolve();
        }

        return ($this->__role() === $other->__role() || $this->class === $other->class)
            && $this->__scope() === $other->__scope();
    }

    /**
     * @template T of object
     * @param T $entity
     * @return self<T>
     */
    public static function fromEntity(object $entity, ?ReferenceInterface $reference = null): self
    {
        /** @phpstan-var self<T> $ref */
        $ref = new self($entity::class, $entity, $reference);
        \assert(true);
        return $ref;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return self<T>
     */
    public static function fromReference(string $class, ReferenceInterface $reference): self
    {
        /** @phpstan-var self<T> $ref */
        $ref = new self($class, null, $reference);
        \assert(true);
        return $ref;
    }
}
