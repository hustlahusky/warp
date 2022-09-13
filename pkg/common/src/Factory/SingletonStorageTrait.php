<?php

declare(strict_types=1);

namespace Warp\Common\Factory;

trait SingletonStorageTrait
{
    /**
     * @var array<class-string<static>,array<string,\WeakReference<static>>>
     */
    private static array $storage = [];

    abstract protected static function singletonKey(mixed $value): string;

    private static function singletonFetch(string $key): ?static
    {
        return (self::$storage[static::class][$key] ?? null)?->get();
    }

    private static function singletonAttach(self $item): void
    {
        $key = static::singletonKey($item);
        // @phpstan-ignore-next-line
        self::$storage[$item::class][$key] = \WeakReference::create($item);
    }

    private static function singletonDetach(self $item): void
    {
        $key = static::singletonKey($item);
        unset(self::$storage[$item::class][$key]);
    }
}
