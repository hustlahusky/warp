<?php

declare(strict_types=1);

namespace Warp\Monad\Option;

use Warp\Monad\Option;

const None = None::none;

/**
 * @template TValue
 * @param TValue $value
 * @return Some<TValue>
 */
function some(mixed $value): Some
{
    return Some::new($value);
}

function none(): None
{
    return None;
}

/**
 * @template TValue
 * @param Option<TValue>|TValue $value
 * @param mixed $noneValue
 * @return Option<TValue>
 */
function optional(mixed $value, mixed $noneValue = null): Option
{
    if ($value instanceof Option) {
        return $value;
    }

    return $noneValue === $value ? None : Some::new($value);
}
