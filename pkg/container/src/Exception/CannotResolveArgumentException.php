<?php

declare(strict_types=1);

namespace Warp\Container\Exception;

use Warp\Container\Factory\Argument;

class CannotResolveArgumentException extends ContainerException
{
    /**
     * @param Argument<mixed> $argument
     */
    public function __construct(Argument $argument, ?\Throwable $previous = null, ?string $reason = null)
    {
        $reason ??= $previous?->getMessage();

        $message = \sprintf('Unable to resolve argument $%s in %s.', $argument->getName(), $argument->getLocation());

        if (null !== $reason) {
            $message .= ' ' . $reason;
        }

        parent::__construct($message, $previous ? $previous->getCode() : 0, $previous);
    }
}
