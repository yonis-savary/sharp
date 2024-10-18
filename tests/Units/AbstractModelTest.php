<?php

namespace YonisSavary\Sharp\Tests\Units;

use PharIo\Manifest\RequiresElement;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ModelQuery;
use YonisSavary\Sharp\Classes\Data\Model;
use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Tests\Models\TestSampleData;
use YonisSavary\Sharp\Tests\Models\TestUser;
use YonisSavary\Sharp\Tests\Models\TestUserData;

class AbstractModelTest extends TestCase
{
    protected function setUp(): void
    {
        resetTestDatabase();
    }

    public static function getSampleModel()
    {
        return new class extends AbstractModel
        {
            public static function getTable(): string
            {
                return "test_user";
            }

            public static function getPrimaryKey(): string
            {
                return "id";
            }

            public static function getFields(): array
            {
                return [
                    "id" => (new DatabaseField("id"))->setType(DatabaseField::INTEGER)->setNullable(false)->hasDefault(false),
                    "login" => (new DatabaseField("id"))->setType(DatabaseField::STRING)->setNullable(false)->hasDefault(false),
                    "password" => (new DatabaseField("id"))->setType(DatabaseField::STRING)->setNullable(false)->hasDefault(false),
                ];
            }
        };
    }

    public function test_getFieldNames()
    {
        $user = self::getSampleModel();

        $this->assertEquals(
            ["id", "login", "password"],
            $user::getFieldNames()
        );
    }

    public function test___construct()
    {
        $user = self::getSampleModel();

        $admin = new $user([
            "id" => 1,
            "login" => "admin",
            "password" => password_hash("admin", PASSWORD_BCRYPT)
        ]);

        $this->assertEquals(1, $admin->id);
        $this->assertEquals("admin", $admin->login);
        $this->assertTrue(password_verify("admin", $admin->password));
    }

    public function test_insert()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(ModelQuery::class, $user::insert());
    }

    public function test_select()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(ModelQuery::class, $user::select());
    }

    public function test_column_format()
    {
        $user = TestUser::select()->where("id", 1)->first();

        $this->assertEquals(
            (object)[
                "id" => 1,
                'login' => 'admin',
                'password' => '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua',
                'salt' => 'dummySalt',
                'blocked' => false
            ]
        , $user->data);
    }

    public function test_update()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(ModelQuery::class, $user::update());
    }

    public function test_delete()
    {
        $user = self::getSampleModel();
        $this->assertInstanceOf(ModelQuery::class, $user::delete());
    }

    public function test_toArray()
    {
        $user = self::getSampleModel();

        /** @var Model $admin */
        $admin = new $user([
            "id" => 1,
            "login" => "admin",
            "password" => "dummy"
        ]);

        $this->assertEquals([
            "data" => [
                "id" => 1,
                "login" => "admin",
                "password" => "dummy"
            ]
        ], $admin->toArray());
    }

    public function test_validate()
    {
        $user = self::getSampleModel();

        $this->assertTrue( (new $user(["id" => 1, "login" => "admin", "password" => "dummy"]))->validate() );
        $this->assertTrue( (new $user(["login" => "admin", "password" => "dummy"]))->validate() );
        $this->assertTrue( (new $user(["id" => 1, "password" => "dummy"]))->validate() );
        $this->assertTrue( (new $user(["id" => 1, "login" => "admin"]))->validate() );
    }

    public function test_insertArray()
    {
        $db = Database::getInstance();

        debug($db->query("SELECT id FROM test_user_data"));
        $nextId = $db->query("SELECT MAX(id) + 1 as next FROM test_user_data")[0]["next"];

        $inserted = TestUserData::insertArray([
            "fk_user" => 1,
            "data" => "someString"
        ]);

        $this->assertEquals($nextId, $inserted);
    }

    public function test_save()
    {
        $id = TestUserData::insertArray([
            "fk_user" => 1,
            "data" => "test_save"
        ]);

        $row = TestUserData::findId($id);
        $this->assertEquals("test_save", $row->data->data);

        $row->data->data = "new_value";

        $row->save();
        $this->assertEquals("new_value", $row->data->data);

        $copy = TestUserData::findId($id);
        $this->assertEquals("new_value", $copy->data->data);
    }

    public function test_save_linked()
    {
        $id = TestUserData::insertArray(["fk_user" => 1, "data" => "test_save_link"]);

        $row = TestUserData::findId($id);
        $row->setLinked(true);

        $this->assertEquals("test_save_link", $row->data->data);

        $row->data = "new_value";

        $copy = TestUserData::findId($id);
        $this->assertEquals("new_value", $copy->data->data);
    }


    public function test_findId()
    {
        $this->assertInstanceOf(AbstractModel::class, TestUser::findId(1));
        $this->assertNull(TestUser::findId(1309809));
    }

    public function test_findWhere()
    {
        $this->assertInstanceOf(AbstractModel::class, TestUser::findWhere(["id" => 1]));
        $this->assertNull(TestUser::findId(["id" => 1309809]));
    }

    public function test_updateId()
    {
        TestUser::updateId(1)->set("login", "testupdate")->fetch();
        $this->assertEquals("testupdate", TestUser::findId(1)->data->login);
    }

    public function test_updateRow()
    {
        TestUser::updateRow(1, ["login" => "testupdaterow"]);
        $this->assertEquals("testupdaterow", TestUser::findId(1)->data->login);
    }

    public function test_deleteId()
    {
        TestUser::insertArray(["login" => "dummy", "password" => "any", "salt" => "any"]);
        $id = Database::getInstance()->lastInsertId();

        $this->assertInstanceOf(AbstractModel::class, TestUser::findId($id));
        TestUser::deleteId($id);
        $this->assertNull(TestUser::findId($id));
    }

    public function test_selectWhere()
    {
        TestUserData::insertArray(["fk_user" => 1, "data" => "someTest"]);
        $this->assertCount(1, TestUserData::selectWhere(["data" => "someTest"]));

        TestUserData::insertArray(["fk_user" => 1, "data" => "someTest"]);
        $this->assertCount(2, TestUserData::selectWhere(["data" => "someTest"]));
    }

    public function test_existsWhere()
    {
        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someExists"]);
        $this->assertTrue(TestUserData::existsWhere(["data" => "someExists"]));

        TestUserData::deleteId($insertedId);
        $this->assertFalse(TestUserData::existsWhere(["data" => "someExists"]));
    }

    public function test_idExists()
    {
        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someIdExists"]);
        $this->assertTrue(TestUserData::idExists($insertedId));

        TestUserData::deleteId($insertedId);
        $this->assertFalse(TestUserData::idExists($insertedId));
    }

    public function test_deleteWhere()
    {
        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someDelete"]);
        $this->assertTrue(TestUserData::idExists($insertedId));

        TestUserData::deleteWhere(["id" => $insertedId]);
        $this->assertFalse(TestUserData::idExists($insertedId));

        $insertedId = TestUserData::insertArray(["fk_user" => 1, "data" => "someDelete"]);
        $this->assertTrue(TestUserData::idExists($insertedId));

        TestUserData::deleteWhere(["data" => "someDelete"]);
        $this->assertFalse(TestUserData::idExists($insertedId));
    }


    public function test_case_routerCanReturnModelData()
    {
        $router = new Router();
        $router->addRoutes(
            Route::get("/", fn() => TestUserData::select()->fetch())
        );

        $req = new Request("GET", "/");
        $res = $router->route($req);

        $res->logSelf();

        $body = json_decode($res->getClientContent(), true);
        $this->assertIsArray($body);

        $this->assertNotNull($body[0]["data"]["fk_user"]);

    }
}