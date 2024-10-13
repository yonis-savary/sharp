<?php

namespace YonisSavary\Sharp\Tests\Units;

use InvalidArgumentException;
use JsonException;
use OutOfRangeException;
use PhpParser\Node\Expr\Cast\Object_;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Storage;

class ObjectArrayTest extends TestCase
{
    public function test_fromArray()
    {
        $this->assertInstanceOf(ObjectArray::class, ObjectArray::fromArray([1,2,3]));

        $this->assertEquals([1,2,3], ObjectArray::fromArray([1,2,3])->collect());
        $this->assertEquals(["A","B","C"], ObjectArray::fromArray(["A","B","C"])->collect());
    }

    public function test_fromExplode()
    {
        $this->assertInstanceOf(ObjectArray::class, ObjectArray::fromExplode(",", "1,2,3"));

        $this->assertEquals(["1","2","3"], ObjectArray::fromExplode(",", "1,2,3")->collect());
    }

    public function test_fromFileLines()
    {
        $store = Storage::getInstance();
        $store->write("ObjectArrayFile.txt", "a\nb\nc\n \n");

        $this->assertEquals(
            ObjectArray::fromFileLines($store->path("ObjectArrayFile.txt"))
            ->length(),
            3
        );

        $this->assertEquals(
            ObjectArray::fromFileLines($store->path("ObjectArrayFile.txt"), false)
            ->length(),
            5
        );
    }

    public function test_fromJSONFile()
    {
        $store = Storage::getInstance();
        $store->write("ObjectArray.json", "[1,2,3]");
        $this->assertEquals(
            ObjectArray::fromJSONFile($store->path("ObjectArray.json"))
            ->collect(),
            [1,2,3]
        );

        $store->write("ObjectArrayInvalid.json", "{'Hello': [1,2,3]}");
        $store->write("ObjectArrayInvalid2.json", "Hello");

        $this->expectException(JsonException::class);
        ObjectArray::fromJSONFile($store->path("ObjectArrayInvalid.json"));

        $this->expectException(JsonException::class);
        ObjectArray::fromJSONFile($store->path("ObjectArrayInvalid2.json"));

        $this->expectException(InvalidArgumentException::class);
        ObjectArray::fromJSONFile($store->path("InexistantObjectArrayFile.json"));
    }

    public function test_writeJSONFile()
    {
        $store = Storage::getInstance();

        ObjectArray::fromArray([1,2,3])
        ->writeJSONFile($store->path("ObjectArrayWrite.json"));

        $this->assertEquals(
            "[1,2,3]",
            $store->read("ObjectArrayWrite.json")
        );
    }

    public function test_writeTextFile()
    {
        $store = Storage::getInstance();

        ObjectArray::fromArray([1,2,3])
        ->writeTextFile($store->path("ObjectArrayWriteA.txt"), ",")
        ->writeTextFile($store->path("ObjectArrayWriteB.txt"), "\n");

        $this->assertEquals("1,2,3"  , $store->read("ObjectArrayWriteA.txt"));
        $this->assertEquals("1\n2\n3", $store->read("ObjectArrayWriteB.txt"));
    }


    public function test_fromQuery()
    {
        $this->assertEquals(
            ['Alfred', 'Francis', 'Martin', 'Quentin', 'Steven'],
            ObjectArray::fromQuery("SELECT name, birth_year FROM test_sample_data")->collect()
        );

        $this->assertEquals(
            [1899, 1939, 1942, 1963, 1946],
            ObjectArray::fromQuery("SELECT birth_year, name FROM test_sample_data")->collect()
        );
    }

    public function test_push()
    {
        $arr = new ObjectArray();
        $arr = $arr->push("A");
        $arr = $arr->push("B", "C");

        $this->assertEquals(["A", "B", "C"], $arr->collect());
    }

    public function test_pop()
    {
        $arr = new ObjectArray([1,2,3]);

        $this->assertEquals([1,2,3], $arr->collect());
        $arr = $arr->pop();
        $this->assertEquals([1,2], $arr->collect());
        $arr = $arr->pop();
        $this->assertEquals([1], $arr->collect());
        $arr = $arr->pop();
        $this->assertEquals([], $arr->collect());
    }

    public function test_shift()
    {
        $arr = new ObjectArray([1,2,3]);

        $this->assertEquals([1,2,3], $arr->collect());
        $arr = $arr->shift();
        $this->assertEquals([2,3], $arr->collect());
        $arr = $arr->shift();
        $this->assertEquals([3], $arr->collect());
        $arr = $arr->shift();
        $this->assertEquals([], $arr->collect());
    }

    public function test_unshift()
    {
        $arr = new ObjectArray();

        $arr = $arr->unshift(3);
        $this->assertEquals([3], $arr->collect());
        $arr = $arr->unshift(2);
        $this->assertEquals([2,3], $arr->collect());
        $arr = $arr->unshift(1);
        $this->assertEquals([1,2,3], $arr->collect());
    }

    public function test_forEach()
    {
        $arr = new ObjectArray([1,2,3,4,5]);
        $acc = 0;

        $arr->forEach(function($n) use (&$acc) { $acc += $n; });
        $this->assertEquals(5+4+3+2+1, $acc);

        // Test by reference
        $arr = new ObjectArray([0,1,2,3,4]);
        $arr->forEach(fn(&$n) => $n = $n+1);
        $this->assertEquals([1,2,3,4,5], $arr->collect());
    }

    public function test_map()
    {
        $arr = new ObjectArray([1,2,3]);
        $transformed = $arr->map(fn($x) => $x*3);

        $this->assertEquals([1,2,3], $arr->collect());
        $this->assertEquals([3,6,9], $transformed->collect());
    }

    public function test_filter()
    {
        $isEven = fn($x) => $x % 2 === 0;

        $arr = new ObjectArray([0,1,2,3,4,5,6,7,8,9]);
        $copy = $arr->filter($isEven);

        $this->assertEquals([0,1,2,3,4,5,6,7,8,9], $arr->collect());
        $this->assertEquals([0,2,4,6,8], $copy->collect());

        $arr = new ObjectArray(["A", "", null, "B", 0, false, "C"]);
        $arr = $arr->filter();
        $this->assertEquals(["A", "B", "C"], $arr->collect());
    }


    public function test_sortByKey()
    {
        $names = ObjectArray::fromArray([
            ["name" => "Malcolm", "age" => 18],
            ["name" => "Melody", "age" => 40],
            ["name" => "Holly", "age" => 35],
            ["name" => "Sylvester", "age" => 80],
            ["name" => "Clyde", "age" => 35],
            ["name" => "Eliot", "age" => 36],
            ["name" => "Peace", "age" => 19],
            ["name" => "Mortimer", "age" => 50],
        ]);

        $sorted = $names->sortByKey(fn($person) => $person["age"])->collect();
        $reversed = $names->sortByKey(fn($person) => $person["age"], true)->collect();

        $this->assertEquals("Sylvester", $sorted[7]["name"]);
        $this->assertEquals("Malcolm", $sorted[0]["name"]);

        $this->assertEquals("Sylvester", $reversed[0]["name"]);
        $this->assertEquals("Malcolm", $reversed[7]["name"]);
    }

    public function test_unique()
    {
        $arr = new ObjectArray([0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9]);
        $copy = $arr->unique();

        $this->assertEquals([0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9], $arr->collect());
        $this->assertEquals([0,1,2,3,4,5,6,7,8,9], $copy->collect());
    }

    public function test_diff()
    {
        $arr = new ObjectArray(["red", "green", "blue"]);
        $copy = $arr->diff(["red"]);

        $this->assertEquals(["red", "green", "blue"], $arr->collect());
        $this->assertEquals(["green", "blue"], $copy->collect());
    }

    public function test_slice()
    {
        $arr = new ObjectArray([1,2,3,4,5]);

        $this->assertEquals([3,4,5], $arr->slice(2)->collect());
        $this->assertEquals([3,4], $arr->slice(2, 2)->collect());
        $this->assertEquals([1,2,3,4,5], $arr->collect());
    }

    public function test_collect()
    {
        $arr = new ObjectArray([1,2,3]);
        $this->assertEquals([1,2,3], $arr->collect());
    }

    public function test_join()
    {
        $arr = new ObjectArray([1,2,3]);
        $this->assertEquals("1,2,3", $arr->join(","));
    }

    public function test_length()
    {
        $arr = new ObjectArray([1,2,3]);

        $this->assertEquals(3, $arr->length());
    }

    public function test_find_and_findIndex()
    {
        $persons = [
            ["name" => "Vincent", "age" => 18],
            ["name" => "Damon",   "age" => 15],
            ["name" => "Hollie",  "age" => 23],
            ["name" => "Percy",   "age" => 14],
            ["name" => "Yvonne",  "age" => 35],
            ["name" => "Jack",    "age" => 56],
        ];

        $arr = new ObjectArray($persons);

        $vincent = $arr->find(fn($x) => $x["age"] === 18);
        $this->assertEquals($persons[0], $vincent);
        $this->assertNull($arr->find(fn($x) => $x["name"] === "Hugo"));

        $this->assertEquals(0,  $arr->findIndex(fn($x) => $x["name"] === "Vincent"));
        $this->assertEquals(5,  $arr->findIndex(fn($x) => $x["age"] === 56));
        $this->assertEquals(-1, $arr->findIndex(fn($x) => $x["name"] === "Bob"));
    }

    public function test_getIndex()
    {
        $arr = new ObjectArray([0,1,2,3]);

        $this->expectException(OutOfRangeException::class);
        $arr->getIndex(-1);
        $arr->getIndex(12);

        for ($i=0; $i<=3; $i++)
            $this->assertEquals($i, $arr->getIndex($i));
    }

    public function test_toAssociative()
    {
        $letters = ["A", "B", "C"];

        $arr = new ObjectArray($letters);

        $results = $arr->toAssociative(fn($value) => [$value, "$value-$value"]);

        $this->assertEquals([
            "A" => "A-A",
            "B" => "B-B",
            "C" => "C-C"
        ], $results);
    }

    public function test_reverse()
    {
        $arr = new ObjectArray([1,2,3]);
        $copy = $arr->reverse();

        $this->assertEquals([1,2,3], $arr->collect());
        $this->assertEquals([3,2,1], $copy->collect());
    }

    public function test_reduce()
    {
        $myArray = new ObjectArray(range(0, 10));

        $this->assertEquals(
            30,
            $myArray->filter(fn($x) => $x%2==0)
            ->reduce(fn($acc, $cur) => $acc + $cur, 0)
        );
    }

    public function test_any()
    {
        $arr = new ObjectArray([1,2,3,4,5]);

        $this->assertTrue($arr->any(fn($x) => $x > 0));
        $this->assertFalse($arr->any(fn($x) => $x < 0));
    }

    public function test_all()
    {
        $arr = new ObjectArray([1,2,3,4,5]);

        $this->assertTrue($arr->all(fn($x) => $x > 0));
        $this->assertFalse($arr->all(fn($x) => $x < 0));
        $this->assertFalse($arr->all(fn($x) => $x === 5));
    }

    public function test_asIntegers()
    {
        $this->assertEquals([1, 2, 3], ObjectArray::fromArray(["1", "2", "3", "abc"])->asIntegers()->collect());
        $this->assertEquals([1, 2, 3, null], ObjectArray::fromArray(["1", "2", "3", "abc"])->asIntegers(false)->collect());
    }

    public function test_asFloats()
    {
        $this->assertEquals([1.9, 2, 3.1416], ObjectArray::fromArray(["1.9", "2", "3.1416", "abc"])->asFloats()->collect());
        $this->assertEquals([1.9, 2, 3.1416, null], ObjectArray::fromArray(["1.9", "2", "3.1416", "abc"])->asFloats(false)->collect());
    }

    public function test_asStrings()
    {
        $this->assertEquals(["1.9", "2", "3.1416", "abc", ""], ObjectArray::fromArray([1.9, 2, 3.1416, "abc", null])->asStrings()->collect());
    }

    public function test_includes()
    {
        $oddsNumbers = ObjectArray::fromArray([0, 2, 4, 6, 8, 10]);

        $this->assertTrue($oddsNumbers->includes(0));
        $this->assertTrue($oddsNumbers->includes(2));

        $this->assertFalse($oddsNumbers->includes(1));
        $this->assertFalse($oddsNumbers->includes("2"));

        $this->assertFalse($oddsNumbers->includes("1", false));
        $this->assertTrue($oddsNumbers->includes("2", false));

    }
}