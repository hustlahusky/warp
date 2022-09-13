<?php

declare(strict_types=1);

namespace Warp\DataSource;

final class IdenticalPropertyExtractor implements PropertyExtractorInterface
{
    public function extractValue(string $name, mixed $value): mixed
    {
        return $value;
    }

    public function extractName(string $name): string
    {
        return $name;
    }
}
