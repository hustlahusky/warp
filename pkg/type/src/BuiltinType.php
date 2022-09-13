<?php

declare(strict_types=1);

namespace Warp\Type;

use Warp\Common\Factory\SingletonStorageTrait;
use Warp\Common\Factory\StaticConstructorInterface;

/**
 * @method static self int()
 * @method static self float()
 * @method static self string()
 * @method static self bool()
 * @method static self resource()
 * @method static self object()
 * @method static self array()
 * @method static self null()
 * @method static self callable()
 * @method static self iterable()
 */
final class BuiltinType implements TypeInterface, StaticConstructorInterface
{
    use SingletonStorageTrait;

    public const INT = 'int';
    public const FLOAT = 'float';
    public const STRING = 'string';
    public const BOOL = 'bool';
    public const RESOURCE = 'resource';
    public const OBJECT = 'object';
    public const ARRAY = 'array';
    public const NULL = 'null';
    public const CALLABLE = 'callable';
    public const ITERABLE = 'iterable';

    public const ALL = [
        self::INT,
        self::FLOAT,
        self::STRING,
        self::BOOL,
        self::RESOURCE,
        self::OBJECT,
        self::ARRAY,
        self::NULL,
        self::CALLABLE,
        self::ITERABLE,
    ];

    private function __construct(
        private readonly string $type,
    ) {
        self::singletonAttach($this);
    }

    public function __destruct()
    {
        self::singletonDetach($this);
    }

    public function __toString(): string
    {
        return $this->type;
    }

    /**
     * Magic factory.
     * @param array{} $arguments
     * @return self
     */
    public static function __callStatic(string $name, array $arguments): self
    {
        return self::new($name);
    }

    public static function new(string $type): self
    {
        if (!\in_array($type, self::ALL, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Argument #1 ($type) should be one of: %s. Got: %s.',
                \implode(', ', self::ALL),
                $type,
            ));
        }

        return self::singletonFetch($type) ?? new self($type);
    }

    public function check(mixed $value): bool
    {
        return match ($this->type) {
            self::INT => \is_int($value),
            self::FLOAT => \is_float($value),
            self::STRING => \is_string($value),
            self::BOOL => \is_bool($value),
            self::RESOURCE => \is_resource($value),
            self::OBJECT => \is_object($value),
            self::ARRAY => \is_array($value),
            self::NULL => null === $value,
            self::CALLABLE => \is_callable($value),
            self::ITERABLE => \is_iterable($value),
            default => false,
        };
    }

    /**
     * @param string|self $value
     */
    protected static function singletonKey(mixed $value): string
    {
        return (string)$value;
    }
}
