<?php

declare(strict_types=1);

namespace Warp\Bridge\Cycle\Mapper\Plugin\GroupData;

use Cycle\ORM\ORMInterface;
use Warp\Bridge\Cycle\Mapper\HydratorMapper;
use Warp\Bridge\Cycle\Mapper\Plugin\ExtractAfterEvent;
use Warp\Bridge\Cycle\Mapper\Plugin\HydrateBeforeEvent;
use Warp\Common\ArrayHelper;

final class GroupDataHandler
{
    public function __construct(
        private readonly ORMInterface $orm,
    ) {
    }

    public function onHydrate(HydrateBeforeEvent $event): void
    {
        $entity = $event->getEntity();
        $mapper = $this->orm->getMapper($entity);
        $namingStrategy = HydratorMapper::getNamingStrategy($mapper);
        // Extract entity fields to prevent issues with partial hydration on update.
        // Also, some hydrators can throw exception, if it is just instantiated empty entity.
        try {
            $extractedData = $mapper->extract($entity);
        } catch (\Throwable) {
            $extractedData = [];
        }

        $data = \iterator_to_array($this->replaceKeys(
            \array_merge($extractedData, $event->getData()),
            static fn (string $offset) => $namingStrategy->hydrate($offset),
        ));

        $data = ArrayHelper::unflatten($data);

        $event->replaceData($data);
    }

    public function onExtract(ExtractAfterEvent $event): void
    {
        $namingStrategy = HydratorMapper::getNamingStrategy($this->orm->getMapper($event->getEntity()));

        $data = ArrayHelper::flatten($event->getData());

        $data = \iterator_to_array($this->replaceKeys(
            $data,
            static fn (string $offset) => $namingStrategy->extract($offset),
        ));

        $event->replaceData($data);
    }

    /**
     * @param array<array-key,mixed> $data
     * @param callable(string):string $replacer
     * @return \Generator<string,mixed>
     */
    private function replaceKeys(array $data, callable $replacer): \Generator
    {
        foreach ($data as $offset => $value) {
            yield $replacer((string)$offset) => $value;
        }
    }
}
