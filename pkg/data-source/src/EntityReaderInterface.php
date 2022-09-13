<?php

declare(strict_types=1);

namespace Warp\DataSource;

use Warp\Collection\CollectionInterface;
use Warp\Criteria\CriteriaInterface;

/**
 * @template E of object
 */
interface EntityReaderInterface
{
    /**
     * Returns entity by its primary field.
     * @return E
     * @throws EntityNotFoundException
     */
    public function findByPrimary(mixed $primary, ?CriteriaInterface $criteria = null): object;

    /**
     * Returns entity collection matching given criteria.
     * @return CollectionInterface<E>
     */
    public function findAll(?CriteriaInterface $criteria = null): CollectionInterface;

    /**
     * Returns first entity matching given criteria.
     * @return E|null
     */
    public function findOne(?CriteriaInterface $criteria = null): ?object;

    /**
     * Counts entities matching given criteria.
     */
    public function count(?CriteriaInterface $criteria = null): int;
}
