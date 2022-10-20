<?php

declare(strict_types=1);

namespace Warp\Monad;

use PHPUnit\Framework\TestCase;

final class MapperChainTest extends TestCase
{
    public function testCompileCached(): void
    {
        $c = new MapperChain();
        self::assertSame($c->compile(), $c->compile());
    }

    public function testEmpty(): void
    {
        $c = new MapperChain();
        $fn = $c->compile();
        self::assertSame(1, $fn(1));
    }

    public function testMap(): void
    {
        $c = new MapperChain();
        $c = $c->map(static fn (int $v): int => $v * 2);
        $fn = $c->compile();
        self::assertSame(2, $fn(1));
    }

    public function testMapMultiple(): void
    {
        $c = new MapperChain();
        $c = $c->map(static fn (int $v): int => $v * 2);
        $c = $c->map(static fn (int $v): int => $v * 2);
        $fn = $c->compile();
        self::assertSame(4, $fn(1));
    }

    public function testFilter(): void
    {
        $c = new MapperChain();
        $c = $c->filter(static fn (int $v): bool => 0 === $v % 2);
        $fn = $c->compile();

        self::assertSame(null, $fn(1));
        self::assertSame(2, $fn(2));
    }

    public function testFilterMultiple(): void
    {
        $c = new MapperChain();
        $c = $c->filter(static fn (int $v): bool => 0 === $v % 2);
        $c = $c->filter(static fn (int $v): bool => $v > 10);
        $fn = $c->compile();

        self::assertSame(null, $fn(1));
        self::assertSame(null, $fn(2));
        self::assertSame(12, $fn(12));
    }

    public function testMapAndFilter(): void
    {
        $c = new MapperChain();
        $c = $c->filter(static fn (int $v): bool => $v > 10);
        $c = $c->map(static fn (int $v): int => $v ** 2);
        $fn = $c->compile();

        self::assertSame(null, $fn(1));
        self::assertSame(121, $fn(11));
    }

    public function testFilteredOutValue(): void
    {
        $c = new MapperChain(false);
        $c = $c->filter(static fn (int $v): bool => 0 === $v % 2);
        $c = $c->map(static fn (int $v): int => $v * 2);
        $fn = $c->compile();

        self::assertSame(false, $fn(1));
        self::assertSame(4, $fn(2));
    }
}
