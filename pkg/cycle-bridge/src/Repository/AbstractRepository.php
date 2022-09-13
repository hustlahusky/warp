<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Repository;

use Cycle\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use Warp\Bridge\Cycle\CycleEntityManager;
use Warp\Collection\CollectionInterface;
use Warp\Criteria\CriteriaInterface;
use Warp\DataSource\DefaultEntityNotFoundExceptionFactory;
use Warp\DataSource\EntityNotFoundExceptionFactoryInterface;
use Warp\DataSource\RepositoryInterface;

/**
 * @template E of object
 * @implements RepositoryInterface<E>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var CycleEntityManager<E>
     */
    protected CycleEntityManager $em;

    /**
     * @param string|class-string $role
     * @phpstan-param class-string<E> $role
     */
    public function __construct(
        protected string $role,
        protected ORMInterface $orm,
        int $transactionMode = TransactionInterface::MODE_CASCADE,
        EntityNotFoundExceptionFactoryInterface $notFoundExceptionFactory = new DefaultEntityNotFoundExceptionFactory(),
    ) {
        $this->em = new CycleEntityManager($this->orm, $transactionMode, $notFoundExceptionFactory);
    }

    public function findByPrimary(mixed $primary, ?CriteriaInterface $criteria = null): object
    {
        return $this->em->getReader($this->role)->findByPrimary($primary, $criteria);
    }

    public function findAll(?CriteriaInterface $criteria = null): CollectionInterface
    {
        return $this->em->getReader($this->role)->findAll($criteria);
    }

    public function findOne(?CriteriaInterface $criteria = null): ?object
    {
        return $this->em->getReader($this->role)->findOne($criteria);
    }

    public function count(?CriteriaInterface $criteria = null): int
    {
        return $this->em->getReader($this->role)->count($criteria);
    }

    public function save(object $entity, object ...$entities): void
    {
        $this->em->getPersister($this->role)->save($entity, ...$entities);
    }

    public function remove(object $entity, object ...$entities): void
    {
        $this->em->getPersister($this->role)->remove($entity, ...$entities);
    }

    public function getMapper(): ORM\MapperInterface
    {
        return $this->orm->getMapper($this->role);
    }
}
