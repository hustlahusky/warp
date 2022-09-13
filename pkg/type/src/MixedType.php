<?php

declare(strict_types=1);

namespace Warp\Type;

use Warp\Common\Factory\SingletonStorageTrait;
use Warp\Common\Factory\StaticConstructorInterface;

final class MixedType implements TypeInterface, StaticConstructorInterface
{
    use SingletonStorageTrait;

    public const NAME = 'mixed';

    private function __construct()
    {
        self::singletonAttach($this);
    }

    public function __destruct()
    {
        self::singletonDetach($this);
    }

    public function __toString(): string
    {
        return self::NAME;
    }

    public function check(mixed $value): bool
    {
        return true;
    }

    public static function new(): self
    {
        return self::singletonFetch(self::NAME) ?? new self();
    }

    protected static function singletonKey(mixed $value): string
    {
        return self::NAME;
    }
}
