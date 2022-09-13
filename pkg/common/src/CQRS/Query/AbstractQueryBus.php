<?php

declare(strict_types=1);

namespace Warp\Common\CQRS\Query;

use Warp\CommandBus\CommandBus as MessageBus;

abstract class AbstractQueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly MessageBus $bus
    ) {
    }

    public function ask(QueryInterface $query): ?ResponseInterface
    {
        return $this->bus->handle($query);
    }
}
