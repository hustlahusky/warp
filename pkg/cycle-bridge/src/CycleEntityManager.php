<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use Warp\DataSource\DefaultEntityNotFoundExceptionFactory;
use Warp\DataSource\EntityNotFoundExceptionFactoryInterface;
use Warp\DataSource\EntityPersisterAggregateInterface;
use Warp\DataSource\EntityPersisterInterface;
use Warp\DataSource\EntityReaderAggregateInterface;
use Warp\DataSource\EntityReaderInterface;

/**
 * @template E of object
 * @implements EntityPersisterInterface<E>
 */
final class CycleEntityManager implements
    EntityReaderAggregateInterface,
    EntityPersisterAggregateInterface,
    EntityPersisterInterface
{
    public function __construct(
        private readonly ORMInterface $orm,
        private readonly int $transactionMode = TransactionInterface::MODE_CASCADE,
        private readonly EntityNotFoundExceptionFactoryInterface $notFoundExceptionFactory = new DefaultEntityNotFoundExceptionFactory(),
    ) {
    }

    public function getReader(string $entity): EntityReaderInterface
    {
        return new CycleEntityReader($this->orm, $entity, $this->notFoundExceptionFactory);
    }

    public function getPersister(?string $entity = null, ?int $transactionMode = null): EntityPersisterInterface
    {
        return new CycleEntityPersister($this->orm, $transactionMode ?? $this->transactionMode);
    }

    public function save(object $entity, object ...$entities): void
    {
        $this->getPersister()->save($entity, ...$entities);
    }

    public function remove(object $entity, object ...$entities): void
    {
        $this->getPersister()->remove($entity, ...$entities);
    }
}
