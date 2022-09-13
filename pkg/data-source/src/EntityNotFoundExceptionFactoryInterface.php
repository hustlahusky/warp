<?php

declare(strict_types=1);

namespace Warp\DataSource;

interface EntityNotFoundExceptionFactoryInterface
{
    /**
     * @param string $entity
     * @param scalar|\Stringable $primary
     * @return EntityNotFoundException
     */
    public function make(string $entity, mixed $primary): EntityNotFoundException;
}
