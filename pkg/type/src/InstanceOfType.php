<?php

declare(strict_types=1);

namespace Warp\Type;

use Warp\Common\Factory\SingletonStorageTrait;
use Warp\Common\Factory\StaticConstructorInterface;

final class InstanceOfType implements TypeInterface, StaticConstructorInterface
{
    use SingletonStorageTrait;

    /**
     * @param class-string $className
     */
    private function __construct(
        private readonly string $className,
    ) {
        self::singletonAttach($this);
    }

    public function __destruct()
    {
        self::singletonDetach($this);
    }

    public function __toString(): string
    {
        return $this->className;
    }

    public function check(mixed $value): bool
    {
        return $value instanceof $this->className;
    }

    /**
     * @param class-string $className
     */
    public static function new(string $className): self
    {
        return self::singletonFetch($className) ?? new self($className);
    }

    /**
     * @param self $value
     */
    protected static function singletonKey(mixed $value): string
    {
        return $value->className;
    }
}
