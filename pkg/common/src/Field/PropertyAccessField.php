<?php

declare(strict_types=1);

namespace Warp\Common\Field;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Warp\Exception\PackageMissingException;

final class PropertyAccessField implements FieldInterface
{
    /**
     * @var PropertyPath<array-key>
     */
    private readonly PropertyPath $propertyPath;
    private readonly PropertyAccessorInterface $propertyAccessor;

    /**
     * @param string|PropertyPath<array-key> $propertyPath
     */
    public function __construct(string|PropertyPath $propertyPath, ?PropertyAccessorInterface $propertyAccessor = null)
    {
        if (!\class_exists(PropertyAccess::class)) {
            throw PackageMissingException::new('symfony/property-access', null, self::class);
        }

        if (!$propertyPath instanceof PropertyPath) {
            $propertyPath = new PropertyPath($propertyPath);
        }

        $this->propertyPath = $propertyPath;
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccessFieldFactory::getDefaultPropertyAccessor();
    }

    public function __toString(): string
    {
        return (string)$this->propertyPath;
    }

    public function getElements(): array
    {
        return $this->propertyPath->getElements();
    }

    public function extract(mixed $element): mixed
    {
        if (!$this->propertyAccessor->isReadable($element, $this->propertyPath)) {
            return null;
        }

        return $this->propertyAccessor->getValue($element, $this->propertyPath);
    }
}
