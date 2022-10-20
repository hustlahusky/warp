<?php

declare(strict_types=1);

namespace Warp\Monad;

use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    public function testIteratorMethodCallsReminder(): void
    {
        $iterator = new \ArrayIterator([1, 2, 3]);

        $actual = [];

        $iterator->rewind();
        while ($iterator->valid()) {
            $current = $iterator->current();
            $key = $iterator->key();
            $actual[$key] = $current;

            $iterator->next();
        }

        self::assertSame([1, 2, 3], $actual);
    }

    public function testEmpty(): void
    {
        $collection = Collection::new();

        self::assertSame([], \iterator_to_array($collection));
    }

    public function testNewFromList(): void
    {
        $range = \range(0, 8);

        $collection = Collection::new($range);

        self::assertSame($range, \iterator_to_array($collection));
    }

    public function testNewFromArray(): void
    {
        $range = \range(0, 8);
        $chars = \array_map(static fn ($i) => \chr($i), $range);
        $array = \array_combine($chars, $range);

        $collection = Collection::new($array);

        self::assertSame($array, \iterator_to_array($collection));
    }

    public function testNewFromGenerator(): void
    {
        $collection = Collection::new((static fn () => yield from \range(0, 9))());

        self::assertSame(\range(0, 9), \iterator_to_array($collection));
    }

    public function testNewFromOtherCollection(): void
    {
        $collection = Collection::new(\range(0, 8));

        self::assertSame($collection, Collection::new($collection));
    }

    public function testMap(): void
    {
        $collection = Collection::new(\range(0, 9));
        $mapCollection = $collection->map(static fn (int $v) => $v * 2);

        self::assertNotSame($collection, $mapCollection);
        self::assertSame(\range(0, 18, 2), \iterator_to_array($mapCollection));
    }

    public function testMapMultiple(): void
    {
        $collection = Collection::new(\range(0, 9));
        $collection = $collection->map(static fn (int $v) => $v * 2);
        $collection = $collection->map(static fn (int $v) => $v * 2);

        self::assertSame(\range(0, 36, 4), \iterator_to_array($collection));
    }

    public function testMapMemory(): void
    {
        $this->skipIfXdebugEnabled();

        $collection = Collection::new(\range(1, 10000));

        $i = 1000;
        while ($i > 0) {
            $collection = $collection->map(static fn (int $v) => $v);
            $i--;
        }

        self::assertSame(\range(1, 10000), \iterator_to_array($collection));
    }

    public function testIndexBy(): void
    {
        $range = \range(0, 8);
        $keyExtractor = static fn ($i) => \chr($i);
        $chars = \array_map($keyExtractor, $range);

        $collection = Collection::new($range);
        $map = $collection->indexBy($keyExtractor);

        self::assertSame(\array_combine($chars, $range), \iterator_to_array($map));
    }

//    public function testIndexByStringable(): void
//    {
//        $stringableFactory = static fn (string $v) => new class($v) {
//            private string $v;
//
//            public function __construct(string $v)
//            {
//                $this->v = $v;
//            }
//
//            public function __toString()
//            {
//                return $this->v;
//            }
//        };
//
//        $array = [
//            [
//                'key' => $stringableFactory('key1'),
//                'field' => 1,
//            ],
//            [
//                'key' => $stringableFactory('key2'),
//                'field' => 2,
//            ],
//            [
//                'key' => $stringableFactory('key3'),
//                'field' => -3,
//            ],
//        ];
//
//        $keyExtractor = new DefaultField('key');
//        $keys = \array_map(static fn ($i) => $keyExtractor->extract($i), $array);
//
//        $collection = Collection::new($array);
//        $map = $collection->indexBy($keyExtractor);
//
//        self::assertSame(\array_combine($keys, $array), \iterator_to_array($map));
//    }

    public function testFilter(): void
    {
        $collection = Collection::new((static fn () => yield from \range(0, 9))());
        $filteredCollection = $collection->filter(static fn (int $v) => $v >= 5);

        self::assertNotSame($collection, $filteredCollection);
        self::assertSame(\array_combine(\range(5, 9), \range(5, 9)), \iterator_to_array($filteredCollection));
        self::assertSame(\range(0, 9), \iterator_to_array($collection));
    }

    public function testCount(): void
    {
        $collection = Collection::new(\range(0, 9));

        self::assertCount(10, $collection);
    }

    public function testCountAfterOperation(): void
    {
        $collection = Collection::new(\range(0, 9))->filter(static fn (int $v) => $v >= 5);

        self::assertCount(5, $collection);
    }

    public function testIterateAndCount(): void
    {
        $collection = Collection::new(\range(0, 9));

        $i = -1;
        foreach ($collection as $item) {
            self::assertSame(++$i, $item);
            self::assertCount(10, $collection);
        }
    }

//    public function testIterateAndRemove(): void
//    {
//        $collection = Collection::new(\range(0, 9));
//
//        foreach ($collection as $value) {
//            $collection->remove($value);
//        }
//
//        self::assertCount(0, $collection);
//    }

    private function skipIfXdebugEnabled(): void
    {
        if (!\extension_loaded('xdebug')) {
            return;
        }

        $xdebugMode = (array)\xdebug_info('mode');

        if ([] === $xdebugMode) {
            return;
        }

        $this->markTestSkipped('Test skipped because xdebug enabled.');
    }
}
