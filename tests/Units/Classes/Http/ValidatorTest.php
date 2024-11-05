<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Http\Classes\Validator;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShow;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

class ValidatorTest extends TestCase
{
    public function test_string()
    {
        $this->assertEquals("bla", Validator::string()->process(" bla ")->getValue());
        $this->assertEquals(" bla ", Validator::string(false)->process(" bla ")->getValue());
    }

    public function test_integer()
    {
        $this->assertEquals(5, Validator::integer()->process("5")->getValue());

        $this->assertFalse(Validator::integer()->process("3.1416")->isValid());
        $this->assertFalse(Validator::integer()->process("bla")->isValid());
    }

    public function test_float()
    {
        $this->assertEquals(5, Validator::float()->process("5")->getValue());
        $this->assertEquals(3.1416, Validator::float()->process("3.1416")->getValue());

        $this->assertFalse(Validator::float()->process("bla")->isValid());
    }

    public function test_email()
    {
        $this->assertTrue(Validator::email()->process("name@domain.com")->isValid());
        $this->assertTrue(Validator::email()->process("name+ads@domain.com")->isValid());
        $this->assertEquals("name@domain.com", Validator::email()->process("name@domain.com")->getValue());
        $this->assertEquals("name+ads@domain.com", Validator::email()->process("name+ads@domain.com")->getValue());

        $this->assertFalse(Validator::email()->process("something else")->isValid());
    }


    public function test_boolean()
    {
        $this->assertTrue(Validator::boolean()->process("on")->getValue());
        $this->assertTrue(Validator::boolean()->process("true")->getValue());
        $this->assertTrue(Validator::boolean()->process("1")->getValue());
        $this->assertTrue(Validator::boolean()->process("yes")->getValue());
        $this->assertTrue(Validator::boolean()->process(true)->getValue());

        $this->assertFalse(Validator::boolean()->process("off")->getValue());
        $this->assertFalse(Validator::boolean()->process("false")->getValue());
        $this->assertFalse(Validator::boolean()->process("0")->getValue());
        $this->assertFalse(Validator::boolean()->process("no")->getValue());
        $this->assertFalse(Validator::boolean()->process(false)->getValue());
        $this->assertFalse(Validator::boolean()->process("something else")->getValue());
    }

    public function test_url()
    {
        $this->assertTrue(Validator::url()->process("https://google.com")->isValid());
        $this->assertTrue(Validator::url()->process("ftp://somehost.com")->isValid());
        $this->assertEquals("https://google.com", Validator::url()->process("https://google.com")->getValue());
        $this->assertEquals("ftp://somehost.com", Validator::url()->process("ftp://somehost.com")->getValue());

        $this->assertFalse(Validator::url()->process("https:/google.com")->isValid());
        $this->assertFalse(Validator::url()->process("https:/google.com")->isValid());
        $this->assertFalse(Validator::url()->process("google")->isValid());
    }

    public function test_date()
    {
        $this->assertTrue(Validator::date()->process("2024-01-01")->isValid());
        $this->assertTrue(Validator::date()->process("0000-01-01")->isValid());
        $this->assertEquals("2024-01-01", Validator::date()->process("2024-01-01")->getValue());
        $this->assertEquals("0000-01-01", Validator::date()->process("0000-01-01")->getValue());

        $this->assertFalse(Validator::date()->process("01-01")->isValid());
        $this->assertFalse(Validator::date()->process("2024-01-01 12:12:12")->isValid());
    }

    public function test_datetime()
    {
        $this->assertTrue(Validator::datetime()->process("2024-01-01 12:12:12")->isValid());
        $this->assertTrue(Validator::datetime()->process("0000-01-01 12:12:12")->isValid());

        $this->assertEquals("2024-01-01 12:12:12", Validator::datetime()->process("2024-01-01 12:12:12")->getValue());
        $this->assertEquals("0000-01-01 12:12:12", Validator::datetime()->process("0000-01-01 12:12:12")->getValue());

        $this->assertFalse(Validator::datetime()->process("2024-01-01")->isValid());
        $this->assertFalse(Validator::datetime()->process("2024-01 12:12:12")->isValid());
    }

    public function test_uuid()
    {
        $this->assertTrue(Validator::uuid()->process("f38af38a-f38a-f38a-f38a-f38af38af38a")->isValid());
        $this->assertEquals("f38af38a-f38a-f38a-f38a-f38af38af38a", Validator::uuid()->process("f38af38a-f38a-f38a-f38a-f38af38af38a")->getValue());

        $this->assertFalse(Validator::uuid()->process("f38af38a")->isValid());
        $this->assertFalse(Validator::uuid()->process("f38af38a-f38a-ZZZZ-f38a-f38af38af38a")->isValid());
    }

    public function test_model()
    {

        $db = TestClassFactory::createDatabase();

        $this->expectException(InvalidArgumentException::class);
        $validator = Validator::model("someclassthatdoesnotexists", true, $db);

        $validator = Validator::model(TestTvShow::class);

        $this->assertTrue($validator->process(1)->isValid());
        $this->assertInstanceOf(TestTvShow::class, $validator->process(1)->getValue());
        $this->assertEquals("Breaking Bad", $validator->process(2)->getValue()->name);

        $this->assertFalse($validator->process(10)->isValid());
        $this->assertFalse($validator->process(-1)->isValid());
    }



    public function test_withCondition()
    {
        $validator = Validator::integer();

        $this->assertInstanceOf(Validator::class, $validator->withCondition(fn($value) => $value == 12, "Value must be 12"));

        $this->assertTrue($validator->process(12)->isValid());
        $this->assertEquals(12, $validator->process(12)->getValue());

        $this->assertTrue($validator->process("12")->isValid());
        $this->assertEquals(12, $validator->process("12")->getValue());

        $this->assertFalse($validator->process("1")->isValid());
        $this->assertFalse($validator->process(1)->isValid());
    }

    public function test_withTransformer()
    {
        $validator = Validator::integer();


        $this->assertInstanceOf(Validator::class, $validator->withTransformer(fn($value) => (int) $value, "Value must be an int"));

        $validator
            ->withTransformer(fn($val) => $val * 5)
            ->withTransformer(fn($val) => $val / 2)
            ->withTransformer(fn($val) => $val + 6)
        ;

        $this->assertEquals(56 , $validator->process(20)->getValue());
        $this->assertEquals(8.5 , $validator->process(1)->getValue());
    }


    public function test_inDictionnary()
    {
        $validator = Validator::string()->inDictionnary(["hello", "goodbye", "hi", "farewell"]);

        $this->assertTrue($validator->process("hello")->isValid());
        $this->assertTrue($validator->process("goodbye")->isValid());
        $this->assertTrue($validator->process("hi")->isValid());
        $this->assertTrue($validator->process("farewell")->isValid());

        $this->assertFalse($validator->process("Hello")->isValid());
        $this->assertFalse($validator->process("bye")->isValid());
    }

    public function test_isBetween()
    {
        $validator = Validator::integer()->isBetween(25, 75);

        $this->assertTrue($validator->process("50")->isValid());
        $this->assertTrue($validator->process("25")->isValid());
        $this->assertTrue($validator->process("75")->isValid());
        $this->assertTrue($validator->process(65)->isValid());
        $this->assertFalse($validator->process(-12)->isValid());
        $this->assertFalse($validator->process(599)->isValid());

        $validator = Validator::integer()->isBetween(25, 75, false);

        $this->assertTrue($validator->process("50")->isValid());
        $this->assertTrue($validator->process(65)->isValid());
        $this->assertFalse($validator->process(-12)->isValid());
        $this->assertFalse($validator->process(599)->isValid());
        $this->assertFalse($validator->process("25")->isValid());
        $this->assertFalse($validator->process("75")->isValid());
    }

    public function test_isIdInTable()
    {
        $db = TestClassFactory::createDatabase();
        $validator = Validator::integer()->isIdInTable(TestTvShow::class, $db);

        $this->assertTrue($validator->process(1)->isValid());
        $this->assertTrue($validator->process(4)->isValid());
        $this->assertFalse($validator->process(10)->isValid());
        $this->assertFalse($validator->process(-2)->isValid());
    }
}