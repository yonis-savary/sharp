<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Data;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ModelQuery;
use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShow;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShowProducer;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestUser;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

class AbstractModelTest extends TestCase
{
    protected Database $database;

    // public function test_getOrCreateForeignObject(){}
    // public function test_setLinked(){}
    // public function test___get(){}
    // public function test___set(){}
    // public function test_jsonSerialize(){}
    // public function test_getTable(){}
    // public function test_getPrimaryKey(){}
    // public function test_getInsertables(){}
    // public function test_getFields(){}
    // public function test_find(){}


    protected function setUp(): void
    {
        $db = TestClassFactory::createDatabase();
        $this->database = $db;
        Database::setInstance($db);
    }

    public static function getSampleModel()
    {
        return new class extends AbstractModel
        {
            public static function getTable(): string
            {
                return 'test_user';
            }

            public static function getPrimaryKey(): string
            {
                return 'id';
            }

            public static function getFields(): array
            {
                return [
                    'id' => (new DatabaseField('id'))->setType(DatabaseField::INTEGER)->setNullable(false)->hasDefault(false),
                    'login' => (new DatabaseField('id'))->setType(DatabaseField::STRING)->setNullable(false)->hasDefault(false),
                    'password' => (new DatabaseField('id'))->setType(DatabaseField::STRING)->setNullable(false)->hasDefault(false),
                ];
            }
        };
    }

    public function test_getFieldNames()
    {
        $user = self::getSampleModel();

        $this->assertEquals(
            ['id', 'login', 'password'],
            $user::getFieldNames()
        );
    }


    public function test_getTable()
    {
        $user = self::getSampleModel();
        $this->assertEquals("test_user", $user::getTable());
    }

    public function test_getPrimaryKey()
    {
        $user = self::getSampleModel();
        $this->assertEquals("id", $user::getPrimaryKey());
    }

    public function test_getInsertables()
    {
        $user = self::getSampleModel();
        $this->assertEquals(["login", "password"], $user::getInsertables());
    }

    public function test_getFields()
    {
        $user = self::getSampleModel();
        $fields = $user::getFields();

        $this->assertCount(3, $fields);
        $this->assertEquals(["id", "login", "password"], array_keys($fields));
    }


    public function test___construct()
    {
        $user = self::getSampleModel();

        $admin = new $user([
            'id' => 1,
            'login' => 'admin',
            'password' => password_hash('admin', PASSWORD_BCRYPT)
        ]);

        $this->assertEquals(1, $admin->id);
        $this->assertEquals('admin', $admin->login);
        $this->assertTrue(password_verify('admin', $admin->password));
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
        $user = TestUser::select()->where('id', 1)->first();

        $this->assertEquals(
            (object)[
                'id' => 1,
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
            'id' => 1,
            'login' => 'admin',
            'password' => 'dummy'
        ]);

        $this->assertEquals([
            'data' => [
                'id' => 1,
                'login' => 'admin',
                'password' => 'dummy'
            ]
        ], $admin->toArray());
    }

    public function test_validate()
    {
        $user = self::getSampleModel();

        $this->assertTrue( (new $user(['id' => 1, 'login' => 'admin', 'password' => 'dummy']))->validate() );
        $this->assertTrue( (new $user(['login' => 'admin', 'password' => 'dummy']))->validate() );
        $this->assertTrue( (new $user(['id' => 1, 'password' => 'dummy']))->validate() );
        $this->assertTrue( (new $user(['id' => 1, 'login' => 'admin']))->validate() );
    }

    public function test_insertArray()
    {
        $nextId = $this->database->query('SELECT MAX(id) + 1 as next FROM test_tv_show')[0]['next'];

        $inserted = TestTvShow::insertArray([
            'name' => "Squid Game",
            'episode_number' => 9
        ]);

        $this->assertEquals($nextId, $inserted);
    }

    public function test_save()
    {
        $id = TestTvShow::insertArray([
            'name' => "Squid Game",
            'episode_number' => 9
        ]);

        $row = TestTvShow::findId($id, true);
        $this->assertEquals('Squid Game', $row->data->name);

        $row->data->name = 'Squid Game - Season 1';

        $row->save();

        $copy = TestTvShow::findId($id, true);
        $this->assertEquals('Squid Game - Season 1', $copy->data->name);
    }

    public function test_save_linked()
    {
        $row = TestTvShow::findId(1, true);
        $row->setLinked(true);
        $row->name = 'new_value';

        $copy = TestTvShow::findId(1, true);
        $this->assertEquals('new_value', $copy->data->name);
    }


    public function test_findId()
    {
        $this->assertInstanceOf(AbstractModel::class, TestUser::findId(1));
        $this->assertNull(TestUser::findId(1309809));
    }

    public function test_findWhere()
    {
        $this->assertInstanceOf(AbstractModel::class, TestUser::findWhere(['id' => 1]));
        $this->assertNull(TestUser::findId(['id' => 1309809]));
    }

    public function test_updateId()
    {
        TestUser::updateId(1)->set('login', 'testupdate')->fetch();
        $this->assertEquals('testupdate', TestUser::findId(1, true, )->data->login);
    }

    public function test_updateRow()
    {
        TestUser::updateRow(1, ['login' => 'testupdaterow']);
        $this->assertEquals('testupdaterow', TestUser::findId(1)->data->login);
    }

    public function test_deleteId()
    {
        $id = TestUser::insertArray(['login' => 'dummy', 'password' => 'any', 'salt' => 'any']);

        $this->assertInstanceOf(AbstractModel::class, TestUser::findId($id));
        TestUser::deleteId($id);
        $this->assertNull(TestUser::findId($id));
    }

    public function test_selectWhere()
    {
        $this->assertCount(2, TestTvShowProducer::selectWhere(['tv_show' => 1]));
        $this->assertCount(3, TestTvShowProducer::selectWhere(['tv_show' => 2]));
    }

    public function test_existsWhere()
    {
        $this->assertTrue(TestTvShowProducer::existsWhere(['tv_show' => 1]));
        $this->assertFalse(TestTvShowProducer::existsWhere(['tv_show' => 10]));
    }

    public function test_idExists()
    {
        $this->assertTrue(TestTvShow::idExists(1));

        TestTvShow::deleteId(1);
        $this->assertFalse(TestTvShow::idExists(1));
    }

    public function test_deleteWhere()
    {
        $this->assertTrue(TestTvShow::idExists(1));

        TestTvShow::deleteWhere(['id' => 1]);
        $this->assertFalse(TestTvShow::idExists(1));

        $insertedId = TestTvShow::insertArray(['name' => "Some random show", 'episode_number' => 749835]);
        $this->assertTrue(TestTvShow::idExists($insertedId));

        TestTvShow::deleteWhere(['name' => 'Some random show']);
        $this->assertFalse(TestTvShow::idExists($insertedId));
    }


    public function test_case_routerCanReturnModelData()
    {
        $router = new Router();
        $router->addRoutes(
            Route::get('/', fn() => TestTvShow::select()->fetch())
        );

        $req = new Request('GET', '/');
        $res = $router->route($req);

        $res->logSelf();

        $body = json_decode($res->getClientContent(), true);
        $this->assertIsArray($body);

        $this->assertNotNull($body[0]['data']['name']);

    }
}