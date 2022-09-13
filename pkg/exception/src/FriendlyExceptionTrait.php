<?php

declare(strict_types=1);

namespace Warp\Exception;

use function Symfony\Component\String\s;

trait FriendlyExceptionTrait
{
    protected string|\Stringable|null $name = null;
    protected string|\Stringable|null $solution = null;

    public function getName(): string
    {
        return (string)($this->name ?? static::getDefaultName());
    }

    public function getSolution(): ?string
    {
        return null === $this->solution ? null : (string)$this->solution;
    }

    protected static function getDefaultName(): string
    {
        $classname = '\\' . static::class;
        $rightSlash = \strrpos($classname, '\\') ?: 0;
        $shortName = \substr($classname, $rightSlash);

        return s($shortName)->snake()->replace('_', ' ')->title()->toString();
    }
}
