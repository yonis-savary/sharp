<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Data;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ModelQuery;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\Classes\QueryField;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShowProducer;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestUser;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

class ModelQueryTest extends TestCase
{
    /*
    TODO TEST public function test_createModel(){}
    TODO TEST public function test_rowCount(){}
    TODO TEST public function test_toObjectArray(){}
    TODO TEST public function test_forEach(){}
    */

    protected function setUp(): void
    {
        $db = TestClassFactory::createDatabase();
        Database::setInstance($db);
    }

    protected function assertBuiltQueryContains(ModelQuery $query, string $needle)
    {
        $built = preg_replace("/\s{2,}/", " ", str_replace("\n", ' ', $query->build()));
        $this->assertStringContainsString($needle, $built);
    }

    protected function assertBuiltQueryNotContains(ModelQuery $query, string $needle)
    {
        $built = preg_replace("/\s{2,}/", " ", str_replace("\n", ' ', $query->build()));
        $this->assertStringNotContainsString($needle, $built);
    }

    public function test_set()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::UPDATE);
        $q->set('field', 5);

        $this->assertBuiltQueryContains($q, "`field` = '5'");
    }

    public function test_setInsertField()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::INSERT);
        $q->setInsertField(['A', 'B', 'C']);

        $this->assertBuiltQueryContains($q, '(`A`,`B`,`C`)');

        $q = TestUser::insert(['A', 'B', 'C']);
        $this->assertBuiltQueryContains($q, '(`A`,`B`,`C`)');
    }

    public function test_insertValues()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::INSERT);
        $q->setInsertField(['A', 'B', 'C']);
        $q->insertValues([1,2,3]);

        $this->assertBuiltQueryContains($q, "('1','2','3')");
    }

    public function test_exploreModel()
    {
        $q = new ModelQuery(TestTvShowProducer::class, ModelQuery::SELECT);
        $q->exploreModel(TestTvShowProducer::class);
        $this->assertBuiltQueryContains($q, '`test_tv_show_producer`.tv_show');
        $this->assertBuiltQueryContains($q, '`test_tv_show_producer`.name');
        $this->assertBuiltQueryContains($q, '`test_tv_show_producer&tv_show`.id');
        $this->assertBuiltQueryContains($q, '`test_tv_show_producer&tv_show`.name');
        $this->assertBuiltQueryContains($q, '`test_tv_show_producer&tv_show`.episode_number');
    }

    public function test_limit()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->limit(500);
        $this->assertBuiltQueryContains($q, 'LIMIT 500');
        $this->assertBuiltQueryNotContains($q, 'OFFSET');

        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->limit(500, 100);
        $this->assertBuiltQueryContains($q, 'LIMIT 500 OFFSET 100');
    }

    public function test_offset()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->limit(500);
        $q->offset(100);

        $this->assertBuiltQueryContains($q, 'LIMIT 500 OFFSET 100');

        $tempLogger = new Logger();
        $originalLogger = Logger::getInstance();

        Logger::setInstance($tempLogger);

        # Offset without query test
        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->offset(100);
        $this->assertBuiltQueryNotContains($q, 'OFFSET 100');

        Logger::setInstance($originalLogger);
    }

    public function test_where()
    {
        $q = (new ModelQuery(TestUser::class, ModelQuery::SELECT))->where('id', 5);
        $this->assertBuiltQueryContains($q, "id = '5'");

        $q = (new ModelQuery(TestUser::class, ModelQuery::SELECT))->where('id', 5, '>');
        $this->assertBuiltQueryContains($q, "id > '5'");

        $q = (new ModelQuery(TestUser::class, ModelQuery::SELECT))->where('id', 5, '=', 'dummy');
        $this->assertBuiltQueryContains($q, "`dummy`.id = '5'");
    }

    public function test_whereSQL()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->whereSQL("roses = 'Red'");
        $this->assertBuiltQueryContains($q, "(roses = 'Red')");

        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->whereSQL("roses = 'Red'");
        $q->whereSQL("violets = 'Blue'");
        $this->assertBuiltQueryContains($q, "(roses = 'Red') AND (violets = 'Blue')");

        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->whereSQL('roses = {}', ['Red']);
        $q->whereSQL('violets = {}', ['Blue']);
        $this->assertBuiltQueryContains($q, "(roses = 'Red') AND (violets = 'Blue')");

        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->whereSQL("roses = 'Red'");
        $q->where('violets', 'Blue');
        $this->assertBuiltQueryContains($q, "(roses = 'Red') AND (violets = 'Blue')");
    }

    public function test_join()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);

        $q->join('LEFT', new QueryField('source', 'field'), '=', 'target', 'targetAlias', 'targetField');

        $this->assertBuiltQueryContains($q, 'LEFT JOIN `target` AS `targetAlias` ON `source`.field = `targetAlias`.targetField');
    }

    public function test_order()
    {
        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->order('test_user', 'field');
        $this->assertBuiltQueryContains($q, 'ORDER BY `test_user`.field ASC');

        $q = new ModelQuery(TestUser::class, ModelQuery::SELECT);
        $q->order('test_user', 'field');
        $q->order('test_user', 'id', 'DESC');
        $this->assertBuiltQueryContains($q, 'ORDER BY `test_user`.field ASC, `test_user`.id DESC');
    }

    public function test_build()
    {
        $q = new ModelQuery(TestTvShowProducer::class, ModelQuery::CREATE);
        $this->assertBuiltQueryContains($q, 'INSERT INTO');

        $q = new ModelQuery(TestTvShowProducer::class, ModelQuery::READ);
        $this->assertBuiltQueryContains($q, 'SELECT FROM');

        $q = new ModelQuery(TestTvShowProducer::class, ModelQuery::UPDATE);
        $this->assertBuiltQueryContains($q, 'UPDATE');

        $q = new ModelQuery(TestTvShowProducer::class, ModelQuery::DELETE);
        $this->assertBuiltQueryContains($q, 'DELETE FROM');

    }

    public function test_first()
    {
        $q = TestTvShowProducer::select();

        $this->assertInstanceOf(AbstractModel::class, $q->first());

        $q->where('id', -1);
        $this->assertNull($q->first());
    }

    public function test_fetch()
    {
        $q = TestTvShowProducer::select();

        $this->assertCount(
            Database::getInstance()->query('SELECT COUNT(*) as max FROM test_tv_show_producer')[0]['max'],
            $q->fetch()
        );


        $q->where('id', -1);
        $this->assertCount(0, $q->fetch());
        $this->assertEquals(0, $q->rowCount());

        $q = TestUser::update();
        $q->set('login', 'blah');
        $q->fetch();
        $this->assertEquals(1, $q->rowCount());

        // Set back the edited login
        TestUser::update()->set('login', 'admin')->fetch();
    }


    public function test_customFields()
    {
        $query = TestTvShowProducer::select(false)
            ->join("INNER", new QueryField("test_tv_show_producer", "tv_show"), "=", "test_tv_show", "test_tv_show_producer&tv_show", "id")
            ->addField("test_tv_show_producer&tv_show", "id")
            ->addField("test_tv_show_producer&tv_show", "name")
            ->addField("test_tv_show_producer&tv_show", "episode_number")
        ;

        $results = $query->first();

        $this->assertNotNull($results->tv_show->data->id ?? null);
        $this->assertNotNull($results->tv_show->data->name ?? null);
        $this->assertNotNull($results->tv_show->data->episode_number ?? null);
    }
}