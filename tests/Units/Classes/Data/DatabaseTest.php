<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Data;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

class DatabaseTest extends TestCase
{
    protected Database $database;

    protected function setUp(): void
    {
        $this->database = TestClassFactory::createDatabase();
    }

    public function test_getConnection()
    {
        $this->assertInstanceOf(
            PDO::class,
            $this->database->getConnection()
        );
    }

    public function test_isConnected()
    {
        $this->assertTrue(
            $this->database->isConnected()
        );
    }

    public function test_getLastStatement()
    {
        $db = $this->database;

        $db->query('SELECT id FROM test_tv_show');

        /** @var PDOStatement $statement */
        $statement = $db->getLastStatement();

        $this->assertInstanceOf(PDOStatement::class, $statement);
        $this->assertIsInt($statement->rowCount());
    }

    public function test_getDriver()
    {
        $db = $this->database;
        $this->assertEquals("sqlite", $db->getDriver());
    }
    public function test_getDatabase()
    {
        $db = $this->database;
        $this->assertNull($db->getDatabase());
    }

    public function test_getHost()
    {
        $db = $this->database;
        $this->assertNull($db->getHost());
    }

    public function test_getPort()
    {
        $db = $this->database;
        $this->assertNull($db->getPort());
    }

    public function test_getUser()
    {
        $db = $this->database;
        $this->assertNull($db->getUser());
    }



    public function test_lastInsertId()
    {
        $db = $this->database;
        $db->query("DELETE FROM sqlite_sequence WHERE name = 'test_tv_show'");
        $db->query('DELETE FROM test_tv_show');

        $db->query('INSERT INTO test_tv_show (name, episode_number) VALUES ({}, {})', ['Some Show', 3058]);
        $this->assertEquals(1, $db->lastInsertId());
    }

    public function test_build()
    {
        $db = $this->database;
        $this->assertEquals("SELECT '1'", $db->build('SELECT {}', [1]));
        $this->assertEquals("SELECT '1'", $db->build('SELECT {}', ['1']));
        $this->assertEquals("SELECT '1'", $db->build("SELECT '{}'", [1]));
        $this->assertEquals("SELECT '1'", $db->build("SELECT '{}'", ['1']));

        $this->assertEquals("SELECT ('1','2','3')", $db->build('SELECT {}', [[1,2,3]]));

        $injection = "'; DELETE FROM user; --";
        $goodQuery = "SELECT ... WHERE login = '''; DELETE FROM user; --'";
        $this->assertEquals($goodQuery, $db->build('SELECT ... WHERE login = {}', [$injection]));
        $this->assertEquals($goodQuery, $db->build("SELECT ... WHERE login = '{}'", [$injection]));

        $this->assertEquals('SELECT TRUE', $db->build('SELECT {}', [true]));
        $this->assertEquals('SELECT FALSE', $db->build('SELECT {}', [false]));
        $this->assertEquals("SELECT 'TRUE'", $db->build("SELECT '{}'", [true]));
        $this->assertEquals("SELECT 'FALSE'", $db->build("SELECT '{}'", [false]));
    }

    public function test_query()
    {
        $db = $this->database;

        $this->assertEquals(
            [[
                'id' => 1,
                'login' => 'admin',
                'password' => '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua',
                'salt' => 'dummySalt',
                'blocked' => false
            ]],
            $db->query('SELECT * FROM test_user')
        );

        $db->query("CREATE TABLE test_one (id INT); CREATE TABLE test_two (id INT);");

        $this->assertTrue($db->hasTable("test_one"));
        $this->assertFalse($db->hasTable("test_two"));
    }

    public function test_exec()
    {
        $db = $this->database;

        $db->exec("CREATE TABLE test_one (id INT); CREATE TABLE test_two (id INT);");

        $this->assertTrue($db->hasTable("test_one"));
        $this->assertTrue($db->hasTable("test_two"));
    }

    public function test_hasTable()
    {
        $db = $this->database;
        $this->assertTrue($db->hasTable('test_user'));
        $this->assertTrue($db->hasTable('test_tv_show'));
        $this->assertFalse($db->hasTable('some_inexistant_table'));
    }

    public function test_hasField()
    {
        $db = $this->database;
        $this->assertTrue ($db->hasField('test_user', 'id'));
        $this->assertFalse($db->hasField('test_user', 'inexistent'));
        $this->assertTrue ($db->hasField('test_tv_show_producer', 'tv_show'));
        $this->assertFalse($db->hasField('test_tv_show_producer', 'inexistent'));
    }
}