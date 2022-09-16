<?php

declare(strict_types=1);

namespace Warp\DevTool\Monorepo\Composer\Synchronizer;

final class VersionConflict
{
    /**
     * @var callable
     */
    private $resolver;

    /**
     * @param array<string,string> $options
     */
    public function __construct(
        private readonly string $message,
        private readonly array $options,
        callable $resolver,
    ) {
        $this->resolver = $resolver;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<string,string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function resolve(string $value): void
    {
        ($this->resolver)($value);
    }
}
