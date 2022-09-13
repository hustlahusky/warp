<?php

declare(strict_types=1);

namespace Warp\Type\Factory;

use Warp\Type\Exception\TypeNotSupportedException;
use Warp\Type\TypeInterface;

final class PartialSupportTypeFactory implements TypeFactoryInterface
{
    use TypeFactoryTrait;

    /**
     * @var callable
     */
    private $supportedPredicate;

    public function __construct(
        private readonly TypeFactoryInterface $factory,
        callable $supportedPredicate,
    ) {
        $this->supportedPredicate = $supportedPredicate;
    }

    public function supports(string $type): bool
    {
        $type = $this->removeWhitespaces($type);
        return $this->factory->supports($type) && ($this->supportedPredicate)($type);
    }

    public function make(string $type): TypeInterface
    {
        $type = $this->removeWhitespaces($type);

        if (!$this->supports($type)) {
            throw new TypeNotSupportedException($type);
        }

        return $this->factory->make($type);
    }
}
