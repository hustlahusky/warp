<?php

declare(strict_types=1);

namespace Warp\Type\Cast;

use Warp\Type\BuiltinType;
use Warp\Type\TypeInterface;

final class ScalarCaster implements CasterInterface
{
    private const SCALAR_TYPES = [
        BuiltinType::INT => BuiltinType::INT,
        BuiltinType::FLOAT => BuiltinType::FLOAT,
        BuiltinType::STRING => BuiltinType::STRING,
        BuiltinType::BOOL => BuiltinType::BOOL,
    ];

    private readonly BuiltinType $type;

    public function __construct(TypeInterface $type)
    {
        if (!self::isScalar($type)) {
            throw new \InvalidArgumentException(\sprintf('Non scalar type (%s) given to ScalarCaster.', $type));
        }

        \assert($type instanceof BuiltinType);
        $this->type = $type;
    }

    public static function isScalar(TypeInterface $type): bool
    {
        return $type instanceof BuiltinType && isset(self::SCALAR_TYPES[(string)$type]);
    }

    public function accepts(mixed $value): bool
    {
        return match ((string)$this->type) {
            BuiltinType::INT => false !== \filter_var($value, \FILTER_VALIDATE_INT),
            BuiltinType::FLOAT => false !== \filter_var($value, \FILTER_VALIDATE_FLOAT),
            BuiltinType::STRING => \is_string($value) || \is_numeric($value) || (
                \is_object($value) && \method_exists($value, '__toString')
            ),
            BuiltinType::BOOL => null !== \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE),
            default => false,
        };
    }

    public function cast(mixed $value): mixed
    {
        if (!$this->accepts($value)) {
            throw new \InvalidArgumentException(\sprintf(
                'Given value (%s) cannot be casted to type %s.',
                \get_debug_type($value),
                $this->type,
            ));
        }

        return match ((string)$this->type) {
            BuiltinType::INT => \filter_var($value, \FILTER_VALIDATE_INT),
            BuiltinType::FLOAT => \filter_var($value, \FILTER_VALIDATE_FLOAT),
            BuiltinType::STRING => (string)$value,
            BuiltinType::BOOL => \filter_var($value, \FILTER_VALIDATE_BOOL),
            default => $value,
        };
    }
}
