<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Select;

use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\ScopeInterface;

final class ReferenceScope implements ScopeInterface
{
    /**
     * @var array<string,mixed>
     */
    private readonly array $where;

    /**
     * @param array<string,mixed> $scope
     * @param array<string,mixed>|null $where
     * @param array<string,SelectQuery::SORT_*> $orderBy
     */
    public function __construct(
        private readonly array $scope,
        ?array $where = null,
        private readonly array $orderBy = [],
    ) {
        $this->where = $where ?? $scope;
    }

    /**
     * @return array<string,mixed>
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    public function apply(QueryBuilder $query): void
    {
        if ([] !== $this->where) {
            $query->andWhere($this->where);
        }
        if ([] !== $this->orderBy) {
            $query->orderBy($this->orderBy);
        }
    }
}
