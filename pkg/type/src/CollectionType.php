<?php

declare(strict_types=1);

namespace Warp\Type;

use Warp\Common\Factory\SingletonStorageTrait;
use Warp\Common\Factory\StaticConstructorInterface;

final class CollectionType implements TypeInterface, StaticConstructorInterface
{
    use SingletonStorageTrait;

    private function __construct(
        private readonly TypeInterface $iterableType,
        private readonly TypeInterface $valueType,
        private readonly ?TypeInterface $keyType = null,
    ) {
        self::singletonAttach($this);
    }

    public function __destruct()
    {
        self::singletonDetach($this);
    }

    public function __toString(): string
    {
        if (
            $this->iterableType instanceof InstanceOfType ||
            $this->valueType instanceof AbstractAggregatedType ||
            null !== $this->keyType
        ) {
            return $this->iterableType . '<' . \implode(',', \array_filter([$this->keyType, $this->valueType])) . '>';
        }

        return $this->valueType . '[]';
    }

    public static function new(
        TypeInterface $valueType,
        ?TypeInterface $keyType = null,
        ?TypeInterface $iterableType = null
    ): self {
        $iterableType ??= BuiltinType::new(BuiltinType::ITERABLE);

        return self::singletonFetch(self::singletonKey([$iterableType, $valueType, $keyType]))
            ?? new self($iterableType, $valueType, $keyType);
    }

    public function check(mixed $value): bool
    {
        if (!$this->iterableType->check($value)) {
            return false;
        }

        foreach ($value as $k => $v) {
            if (!$this->valueType->check($v)) {
                return false;
            }

            if (null !== $this->keyType && !$this->keyType->check($k)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param self|array{TypeInterface,TypeInterface,TypeInterface|null} $value
     */
    protected static function singletonKey(mixed $value): string
    {
        if ($value instanceof self) {
            $iterableType = $value->iterableType;
            $valueType = $value->valueType;
            $keyType = $value->keyType;
        } else {
            [$iterableType, $valueType, $keyType] = $value;
        }

        return \implode(':', [$iterableType, $valueType, $keyType]);
    }
}
