<?php

declare(strict_types=1);

namespace Warp\Type\Exception;

final class TypeNotSupportedException extends \InvalidArgumentException
{
    public function __construct(string $type, ?string $typeClass = null)
    {
        parent::__construct(\sprintf('Type "%s" is not supported%s', $type, $typeClass ? 'by ' . $typeClass : ''));
    }
}
