<?php

declare(strict_types=1);

namespace Warp\DataSource;

interface PropertyExtractorInterface
{
    public function extractValue(string $name, mixed $value): mixed;

    public function extractName(string $name): string;
}
