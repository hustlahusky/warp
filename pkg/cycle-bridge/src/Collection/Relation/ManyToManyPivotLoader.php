<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Collection\Relation;

use Cycle\Database\Injection\Parameter;
use Cycle\ORM\Heap\Node;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\ReferenceInterface;
use Cycle\ORM\Select;
use Warp\Bridge\Cycle\NodeHelper;

/**
 * @internal
 */
final class ManyToManyPivotLoader
{
    private readonly string $role;

    /**
     * @var Node[]
     */
    private array $outerNodes = [];

    private bool $loaded = false;

    /**
     * @var array<string,object>
     */
    private array $loadedPivots = [];

    public function __construct(
        private readonly ORMInterface $orm,
        string $role,
        private readonly string $innerKey,
        private readonly string $outerKey,
        private readonly string $throughInnerKey,
        private readonly string $throughOuterKey,
        private readonly Node $innerNode
    ) {
        $this->role = $this->orm->resolveRole($role);
    }

    public function addOuterNode(Node $node): void
    {
        if ($this->loaded) {
            throw new \RuntimeException('Cannot add outer nodes after pivot context loaded.');
        }

        $this->outerNodes[] = $node;
    }

    public function getPivot(Node $outerNode, mixed $pivot = null): object
    {
        $this->loadPivotContext();

        $offset = $this->fetchKey($outerNode, $this->outerKey);

        $realPivot = null === $offset ? null : $this->loadedPivots[$offset] ?? null;

        return $this->hydratePivot($this->initPivot($outerNode, $realPivot ?? $pivot), $pivot);
    }

    private function loadPivotContext(): void
    {
        if ($this->loaded) {
            return;
        }

        if (null === $where = $this->makeWhereScope()) {
            return;
        }

        $select = new Select($this->orm, $this->role);
        $select->where($where);

        $this->loaded = true;
        $this->loadedPivots = $this->indexPivots($select);
        $this->outerNodes = [];
    }

    /**
     * @return array<string,mixed>|null
     */
    private function makeWhereScope(): ?array
    {
        if (!NodeHelper::nodePersisted($this->innerNode)) {
            return null;
        }

        if (null === $innerValue = $this->fetchKey($this->innerNode, $this->innerKey)) {
            return null;
        }

        $outerValues = [];
        foreach ($this->outerNodes as $outerNode) {
            if (!NodeHelper::nodePersisted($outerNode)) {
                continue;
            }

            if (null === $outerValue = $this->fetchKey($outerNode, $this->outerKey)) {
                continue;
            }

            $outerValues[] = $outerValue;
        }

        if ([] === $outerValues) {
            return null;
        }

        return [
            $this->throughInnerKey => $innerValue,
            $this->throughOuterKey => new Parameter($outerValues),
        ];
    }

    /**
     * @param object[] $pivots
     * @return object[]
     */
    private function indexPivots(iterable $pivots): array
    {
        $output = [];

        foreach ($pivots as $pivot) {
            $node = $this->getNode($pivot);
            $offset = $this->fetchKey($node, $this->throughOuterKey);
            \assert(null !== $offset);
            $output[$offset] = $pivot;
        }

        return $output;
    }

    private function fetchKey(Node $node, string $key): mixed
    {
        return $node->getData()[$key] ?? null;
    }

    /**
     * Since many-to-many relation can overlap from two directions we have to properly resolve the pivot entity upon
     * its generation. This is achieved using temporary mapping associated with each of the entity states.
     */
    private function initPivot(Node $outerNode, mixed $pivot): object
    {
        [$source, $target] = $this->sortRelation($this->innerNode, $outerNode);

        $relationStorage = $source->getState()->getStorage($this->role);

        if ($relationStorage->contains($target)) {
            return $relationStorage->offsetGet($target);
        }

        $pivot = $this->makePivot($pivot);

        $pNode = $this->getNode($pivot, $this->role);
        if (null !== $offset = $this->fetchKey($pNode, $this->throughOuterKey)) {
            $this->loadedPivots[(string)$offset] = $pivot;
        }

        $relationStorage->offsetSet($target, $pivot);

        return $pivot;
    }

    /**
     * Keep only one relation branch as primary branch.
     * @return array{Node,Node}
     */
    private function sortRelation(Node $node, Node $related): array
    {
        // always use single storage
        if ($related->getState()->getStorage($this->role)->contains($node)) {
            return [$related, $node];
        }

        return [$node, $related];
    }

    private function makePivot(mixed $pivot): object
    {
        if (\is_array($pivot) || null === $pivot) {
            $pivot = $this->orm->make($this->role, $pivot ?? []);
            \assert(null !== $pivot);
        } elseif (!\is_object($pivot)) {
            throw new \RuntimeException(\sprintf(
                'Argument #3 ($pivot) expected to be an object, array or null. Got: %s.',
                \get_debug_type($pivot)
            ));
        }

        return $pivot;
    }

    private function hydratePivot(object $pivot, mixed $data): object
    {
        if (null === $data) {
            return $pivot;
        }

        $mapper = $this->orm->getMapper($this->role);

        if (\is_object($data)) {
            $data = $mapper->extract($data);
        }

        if (!\is_array($data)) {
            return $pivot;
        }

        return $mapper->hydrate($pivot, $data);
    }

    private function getNode(object $entity, ?string $role = null): Node
    {
        if ($entity instanceof PromiseInterface && $entity->__loaded()) {
            $entity = $entity->__resolve();
        }

        if ($entity instanceof ReferenceInterface) {
            return new Node(Node::PROMISED, $entity->__scope(), $entity->__role());
        }

        $node = $this->orm->getHeap()->get($entity);

        if (null === $node) {
            // possibly rely on relation target role, it will allow context switch
            $node = new Node(Node::NEW, [], $role ?? $this->orm->resolveRole($entity));
            $this->orm->getHeap()->attach($entity, $node);
        }

        return $node;
    }
}
