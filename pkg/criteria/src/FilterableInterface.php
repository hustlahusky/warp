<?php

declare(strict_types=1);

namespace Warp\Criteria;

/**
 * @todo maybe add generic template for matching() return.
 */
interface FilterableInterface
{
    public function matching(CriteriaInterface $criteria): mixed;
}
