<?php

declare(strict_types=1);

namespace Warp\Criteria\Expression;

use Warp\Common\Field\FieldInterface;
use Webmozart\Expression\Expression;
use Webmozart\Expression\Logic\AndX;
use Webmozart\Expression\Logic\OrX;

final class Selector extends \Webmozart\Expression\Selector\Selector
{
    public function __construct(
        private readonly FieldInterface $field,
        Expression $expr,
    ) {
        parent::__construct($expr);
    }

    public function evaluate(mixed $value): bool
    {
        return $this->expr->evaluate($this->field->extract($value));
    }

    public function toString(): string
    {
        $exprString = $this->expr->toString();

        if ($this->expr instanceof AndX || $this->expr instanceof OrX) {
            return $this->field . '{' . $exprString . '}';
        }

        // Append "functions" with "."
        if (isset($exprString[0]) && \ctype_alpha($exprString[0])) {
            return $this->field . '.' . $exprString;
        }

        return $this->field . $exprString;
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }
}
