<?php

declare(strict_types=1);

namespace Warp\Common\Field;

use Warp\Exception\PackageMissingException;
use Yiisoft\Arrays\ArrayHelper;

final class YiiField implements FieldInterface
{
    /**
     * @var string[]
     */
    private readonly array $elements;

    /**
     * @var array-key|list<array-key>|\Closure
     */
    private mixed $extractKey;

    /**
     * @param array-key|list<array-key>|\Closure|null $extractKey
     */
    public function __construct(
        private readonly string $field,
        mixed $extractKey = null,
    ) {
        if (!\class_exists(ArrayHelper::class)) {
            throw PackageMissingException::new('yiisoft/arrays', null, self::class);
        }
        $this->elements = DefaultField::parseElements($field);
        $this->extractKey = $extractKey ?? $this->elements;
    }

    public function __toString(): string
    {
        return $this->field;
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function extract(mixed $element): mixed
    {
        return ArrayHelper::getValue($element, $this->extractKey);
    }
}
