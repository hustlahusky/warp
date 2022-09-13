<?php

declare(strict_types=1);

namespace Warp\Type\Cast;

use Warp\Type\BuiltinType;
use Warp\Type\TypeInterface;

final class NullCasterFactory implements CasterFactoryInterface
{
    /**
     * @phpstan-param NullCaster::ACCEPT_* $accept
     */
    public function __construct(
        private readonly string $accept = NullCaster::ACCEPT_EMPTY,
    ) {
    }

    public function make(TypeInterface $type): ?CasterInterface
    {
        if (BuiltinType::null() === $type) {
            return new NullCaster($this->accept);
        }

        return null;
    }
}
