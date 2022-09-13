<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Relation;
use Cycle\ORM\SchemaInterface;
use Warp\Bridge\Cycle\NodeHelper;
use Warp\DataSource\PropertyExtractorInterface;

/**
 * @internal
 */
final class CyclePropertyExtractor implements PropertyExtractorInterface
{
    public function __construct(
        private readonly ORMInterface $orm,
        private readonly string $role,
    ) {
    }

    public function extractValue(string $name, mixed $value): mixed
    {
        $name = $this->getPropertyExtractor()->extractName($name);
        [$relation, $property] = $this->splitName($name);
        return $this->getPropertyExtractor($relation)->extractValue($property, $value);
    }

    public function extractName(string $name): string
    {
        $name = $this->getPropertyExtractor()->extractName($name);
        [$relation, $property] = $this->splitName($name);
        $extractor = $this->getPropertyExtractor($relation);
        $field = $extractor->extractName($property);
        ($extractor instanceof self ? $extractor : $this)->assertFieldDefined($field);
        return (null === $relation ? '' : $relation . '.') . $field;
    }

    /**
     * @return array<array-key,mixed>|null
     */
    public function getRelationSchemaIfExists(string $name): ?array
    {
        $name = $this->getPropertyExtractor()->extractName($name);
        [$relation, $property] = $this->splitName($name);

        if (null !== $relation) {
            return $this->getRelationPropertyExtractor($relation)->getRelationSchemaIfExists($property);
        }

        if (!$this->hasRelation($property)) {
            return null;
        }

        return $this->orm->getSchema()->defineRelation($this->role, $property);
    }

    public function fetchKey(string $key, object $entity): mixed
    {
        $node = $this->orm->getHeap()->get($entity);

        if (null === $node || !NodeHelper::nodePersisted($node)) {
            throw new \RuntimeException('Could not fetch key from entity, because it not managed by orm.');
        }

        return $node->getData()[$key];
    }

    public function getRole(object $entity): string
    {
        return $this->orm->resolveRole($entity);
    }

    /**
     * @return array{string|null,string}
     */
    private function splitName(string $name): array
    {
        /**
         * @var string $left
         * @var string|null $right
         */
        [$left, $right] = \explode('.', $name, 2) + ['', null];

        return null === $right
            ? [null, $left]
            : [$left, $right];
    }

    private function getPropertyExtractor(?string $relation = null): PropertyExtractorInterface
    {
        if (null !== $relation) {
            return $this->getRelationPropertyExtractor($relation);
        }

        return HydratorMapper::getPropertyExtractor(
            $this->orm->getSchema()->defines($this->role) ? $this->orm->getMapper($this->role) : null
        );
    }

    private function getRelationPropertyExtractor(string $relation): self
    {
        $relSchema = $this->orm->getSchema()->defineRelation($this->role, $relation);

        return new self($this->orm, $relSchema[Relation::TARGET]);
    }

    private function hasRelation(string $relation): bool
    {
        $schema = $this->orm->getSchema();
        return $schema->defines($this->role) && \in_array($relation, $schema->getRelations($this->role), true);
    }

    private function assertFieldDefined(string $field): void
    {
        if (\str_contains($field, '.')) {
            return;
        }

        $fields = $this->orm->getSchema()->define($this->role, SchemaInterface::COLUMNS);

        if (isset($fields[$field]) || \in_array($field, $fields, true)) {
            return;
        }

        throw new \InvalidArgumentException(\sprintf('Entity "%s" has not field "%s".', $this->role, $field));
    }
}
