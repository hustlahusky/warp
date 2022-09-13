<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Collection\Relation;

use Cycle\ORM\Heap\Node;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Relation;
use Cycle\ORM\Relation\AbstractRelation;
use Warp\Bridge\Cycle\Collection\CollectionFactoryInterface;
use Warp\Bridge\Cycle\Collection\ObjectCollectionInterface;
use Warp\Bridge\Cycle\Collection\ObjectCollectionPromiseInterface;
use Warp\Bridge\Cycle\Collection\ObjectStorage;

abstract class AbstractToManyRelation extends AbstractRelation implements ToManyRelationInterface
{
    /**
     * @phpstan-var ORMInterface
     */
    protected $orm;

    /**
     * @var array<int,\WeakReference<Node>>
     */
    private array $promiseNodeMap = [];

    /**
     * @param array<array-key,mixed> $schema
     */
    public function __construct(
        ORMInterface $orm,
        string $name,
        string $target,
        array $schema,
        protected CollectionFactoryInterface $collectionFactory,
    ) {
        parent::__construct($orm, $name, $target, $schema);
    }

    public function getRelationType(): int
    {
        return $this->schema[Relation::TYPE];
    }

    /**
     * @param ObjectCollectionPromiseInterface<object,mixed> $promise
     * @internal
     */
    protected function linkNodeToPromise(Node $node, ObjectCollectionPromiseInterface $promise): void
    {
        $this->promiseNodeMap[$promise->__id()] = \WeakReference::create($node);

        foreach ($this->promiseNodeMap as $k => $ref) {
            if (null !== $ref->get()) {
                continue;
            }
            unset($this->promiseNodeMap[$k]);
        }
    }

    /**
     * @param ObjectCollectionPromiseInterface<object,mixed> $promise
     * @internal
     */
    protected function getNodeByPromise(ObjectCollectionPromiseInterface $promise): ?Node
    {
        return ($this->promiseNodeMap[$promise->__id()] ?? null)?->get();
    }

    /**
     * Override promise in relation in node with loaded snapshot.
     * @param ObjectCollectionPromiseInterface<object,mixed> $promise
     * @param ObjectCollectionInterface<object,mixed> $collection
     * @internal
     */
    protected function linkPromiseSnapshot(
        ObjectCollectionPromiseInterface $promise,
        ObjectCollectionInterface $collection
    ): void {
        $node = $this->getNodeByPromise($promise);

        // it's ok that node is nullable, because relation can load cloned collection via withScope() method.
        if (null === $node) {
            return;
        }

        $node->setRelation($this->name, ObjectStorage::snapshot($collection));
    }
}
