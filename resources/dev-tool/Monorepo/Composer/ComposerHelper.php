<?php

declare(strict_types=1);

namespace Warp\DevTool\Monorepo\Composer;

use Symfony\Component\Console\Helper\Helper;

final class ComposerHelper extends Helper
{
    public const NAME = 'composer';

    private ?ComposerJson $composerJson = null;

    public function __construct(
        private readonly string $composerJsonPath,
    ) {
    }

    public function getComposerJson(): ComposerJson
    {
        return $this->composerJson ??= ComposerJson::read($this->composerJsonPath);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
