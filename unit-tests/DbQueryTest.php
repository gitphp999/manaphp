<?php

use ManaPHP\Db\Query;

defined('UNIT_TESTS_ROOT') || require __DIR__ . '/bootstrap.php';

class DbQueryTest extends TestCase
{
    public function setUp()
    {
        $di = new \ManaPHP\Di\FactoryDefault();

        $config = require __DIR__ . '/config.database.php';
        $di->db = $db = new ManaPHP\Db\Adapter\Mysql($config['mysql']);
        // $this->db = new ManaPHP\Db\Adapter\Sqlite($config['sqlite']);

        $db->attachEvent('db:beforeQuery', function (\ManaPHP\DbInterface $source, $data) {
            //  var_dump(['sql'=>$source->getSQL(),'bind'=>$source->getBind()]);
            var_dump($source->getSQL(), $source->getEmulatedSQL(2));

        });

        echo get_class($db), PHP_EOL;
    }

    public function test_select()
    {
        $this->assertEquals('SELECT * FROM [city]',
            (new Query())->from('city')->getSql());

        $this->assertEquals('SELECT * FROM [city]',
            (new Query())->select('*')->from('city')->getSql());

        $this->assertEquals('SELECT [city_id], [city_name] FROM [city]',
            (new Query())->select('city_id, city_name')->from('city')->getSql());

        $this->assertEquals('SELECT [city_id] AS [id], [city_name] FROM [city]',
            (new Query())->select('city_id as id, city_name')->from('city')->getSql());

        $this->assertEquals('SELECT [city_id], [city_name] FROM [city]',
            (new Query())->select(['city_id', 'city_name'])->from('city')->getSql());

        $this->assertEquals('SELECT [city_id] AS [id], [city_name] FROM [city]',
            (new Query())->select(['id' => 'city_id', 'city_name'])->from('city')->getSql());

        $this->assertEquals('SELECT SUM(city_id) AS [sum], [city_name] FROM [city]',
            (new Query())->select(['sum' => 'SUM(city_id)', 'city_name'])->from('city')->getSql());

        $this->assertEquals('SELECT [c].[city_id] FROM [city] AS [c]',
            (new Query())->select('c.city_id')->from('city', 'c')->getSql());

        $this->assertEquals('SELECT [city_id] [id] FROM [city]',
            (new Query())->select('city_id id')->from('city')->getSql());
    }

    public function test_from()
    {
        $this->assertEquals('SELECT * FROM [city]',
            (new Query())->from('city')->getSql());

        $this->assertEquals('SELECT * FROM [city] AS [c]',
            (new Query())->from('city', 'c')->getSql());

        $this->assertEquals('SELECT * FROM [db].[city]',
            (new Query())->from('db.city')->getSql());

        $this->assertEquals('SELECT * FROM [db].[city] AS [c]',
            (new Query())->from('db.city', 'c')->getSql());
    }

    public function test_join()
    {
        $this->assertEquals('SELECT * FROM [city] LEFT JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->join('country', 'country.city_id=city.city_id', null, 'LEFT')->getSql());

        $this->assertEquals('SELECT * FROM [city] LEFT JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->join('country', 'country.city_id=city.city_id', null, 'LEFT')->getSql());

        $this->assertEquals('SELECT * FROM [city] AS [c1] LEFT JOIN [country] AS [c2] ON [c2].[city_id]=[c1].[city_id]',
            (new Query())->from('city', 'c1')->join('country', 'c2.city_id=c1.city_id', 'c2', 'LEFT')->getSql());
    }

    public function test_innerJoin()
    {
        $this->assertEquals('SELECT * FROM [city] INNER JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->innerJoin('country', 'country.city_id=city.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] INNER JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->innerJoin('country', 'country.city_id=city.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] AS [c1] INNER JOIN [country] AS [c2] ON [c2].[city_id]=[c1].[city_id]',
            (new Query())->from('city', 'c1')->innerJoin('country', 'c2.city_id=c1.city_id', 'c2')->getSql());
    }

    public function test_leftJoin()
    {
        $this->assertEquals('SELECT * FROM [city] LEFT JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->leftJoin('country', 'country.city_id=city.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] LEFT JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->leftJoin('country', 'country.city_id=city.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] AS [c1] LEFT JOIN [country] AS [c2] ON [c2].[city_id]=[c1].[city_id]',
            (new Query())->from('city', 'c1')->leftJoin('country', 'c2.city_id=c1.city_id', 'c2')->getSql());
    }

    public function test_rightJoin()
    {
        $this->assertEquals('SELECT * FROM [city] RIGHT JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->rightJoin('country', 'country.city_id=city.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] RIGHT JOIN [country] ON [country].[city_id]=[city].[city_id]',
            (new Query())->from('city')->rightJoin('country', 'country.city_id=city.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] AS [c1] RIGHT JOIN [country] AS [c2] ON [c2].[city_id]=[c1].[city_id]',
            (new Query())->from('city', 'c1')->rightJoin('country', 'c2.city_id=c1.city_id', 'c2')->getSql());
    }

    public function test_where()
    {
        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]=:city_id',
            (new Query())->from('city')->where('city_id', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]=:city_id',
            (new Query())->from('city')->where('city_id=', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]!=:city_id',
            (new Query())->from('city')->where('city_id!=', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]>:city_id',
            (new Query())->from('city')->where('city_id>', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]>=:city_id',
            (new Query())->from('city')->where('city_id>=', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]<:city_id',
            (new Query())->from('city')->where('city_id<', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id]<=:city_id',
            (new Query())->from('city')->where('city_id<=', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [c].[city_id]=:c_city_id',
            (new Query())->from('city')->where('c.city_id', 1)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [c].[city_id]>=:c_city_id',
            (new Query())->from('city')->where('c.city_id >=', 1)->getSql());
    }

    public function test_betweenWhere()
    {
        $this->assertEquals('SELECT * FROM [city] WHERE [city_id] BETWEEN :_min_0 AND :_max_0',
            (new Query())->from('city')->betweenWhere('city_id', 1, 10)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [c].[city_id] BETWEEN :_min_0 AND :_max_0',
            (new Query())->from('city')->betweenWhere('c.city_id', 1, 10)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE DATE(created_time) BETWEEN :_min_0 AND :_max_0',
            (new Query())->from('city')->betweenWhere('DATE(created_time)', 2000, 2100)->getSql());
    }

    public function test_notBetweenWhere()
    {
        $this->assertEquals('SELECT * FROM [city] WHERE [city_id] NOT BETWEEN :_min_0 AND :_max_0',
            (new Query())->from('city')->notBetweenWhere('city_id', 1, 10)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [c].[city_id] NOT BETWEEN :_min_0 AND :_max_0',
            (new Query())->from('city')->notBetweenWhere('c.city_id', 1, 10)->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE DATE(created_time) NOT BETWEEN :_min_0 AND :_max_0',
            (new Query())->from('city')->notBetweenWhere('DATE(created_time)', 2000, 2100)->getSql());
    }

    public function test_inWhere()
    {
        $this->assertEquals('SELECT * FROM [city] WHERE 1=2',
            (new Query())->from('city')->inWhere('city_id', [])->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id] IN (:_in_0_0)',
            (new Query())->from('city')->inWhere('city_id', [1])->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id] IN (:_in_0_0, :_in_0_1)',
            (new Query())->from('city')->inWhere('city_id', [1, 2])->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE DATE(created_time) IN (:_in_0_0, :_in_0_1)',
            (new Query())->from('city')->inWhere('DATE(created_time)', [2000, 2001])->getSql());
    }

    public function test_notInWhere()
    {
        $this->assertEquals('SELECT * FROM [city]',
            (new Query())->from('city')->notInWhere('city_id', [])->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE [city_id] NOT IN (:_in_0_0)',
            (new Query())->from('city')->notInWhere('city_id', [1])->getSql());
        $this->assertEquals('SELECT * FROM [city] WHERE [city_id] NOT IN (:_in_0_0, :_in_0_1)',
            (new Query())->from('city')->notInWhere('city_id', [1, 2])->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE DATE(created_time) NOT IN (:_in_0_0, :_in_0_1)',
            (new Query())->from('city')->notInWhere('DATE(created_time)', [2000, 2001])->getSql());
    }

    public function test_likeWhere()
    {
        $this->assertEquals('SELECT * FROM [city] WHERE [city_name] LIKE :city_name',
            (new Query())->from('city')->likeWhere('city_name', '%A%')->getSql());

        $this->assertEquals('SELECT * FROM [city] AS [c] WHERE [c].[city_name] LIKE :c_city_name',
            (new Query())->from('city', 'c')->likeWhere('c.city_name', '%A%')->getSql());

        $this->assertEquals('SELECT * FROM [city] WHERE ([city_name] LIKE :city_name OR [country_name] LIKE :country_name)',
            (new Query())->from('city')->likeWhere(['city_name', 'country_name'], '%A%')->getSql());
    }

    public function test_limit()
    {
        $this->assertEquals('SELECT * FROM [city] LIMIT 10',
            (new Query())->from('city')->limit(10)->getSql());

        $this->assertEquals('SELECT * FROM [city] LIMIT 10 OFFSET 20',
            (new Query())->from('city')->limit(10, 20)->getSql());

        $this->assertEquals('SELECT * FROM [city] LIMIT 10 OFFSET 20',
            (new Query())->from('city')->limit('10', '20')->getSql());
    }

    public function test_page()
    {
        $this->assertEquals('SELECT * FROM [city] LIMIT 10',
            (new Query())->from('city')->page(10)->getSql());

        $this->assertEquals('SELECT * FROM [city] LIMIT 10 OFFSET 20',
            (new Query())->from('city')->page(10, 3)->getSql());

        $this->assertEquals('SELECT * FROM [city] LIMIT 10 OFFSET 20',
            (new Query())->from('city')->page('10', '3')->getSql());
    }

    public function test_orderBy()
    {
        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id]',
            (new Query())->from('city')->orderBy('city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] ASC',
            (new Query())->from('city')->orderBy('city_id ASC')->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] DESC',
            (new Query())->from('city')->orderBy('city_id DESC')->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] ASC',
            (new Query())->from('city')->orderBy(['city_id'])->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] ASC',
            (new Query())->from('city')->orderBy(['city_id' => SORT_ASC])->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] ASC, [city_name] DESC',
            (new Query())->from('city')->orderBy(['city_id' => SORT_ASC, 'city_name' => 'DESC'])->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] DESC',
            (new Query())->from('city')->orderBy(['city_id' => SORT_DESC])->getSql());

        $this->assertEquals('SELECT * FROM [city] ORDER BY [city_id] ASC',
            (new Query())->from('city')->orderBy(['city_id' => 'ASC'])->getSql());
    }

    public function test_having()
    {
        $this->assertEquals('SELECT COUNT(*), city_id FROM [country] GROUP BY [city_id] HAVING COUNT(*) >10',
            (new Query())->select('COUNT(*), city_id')->from('country')->groupBy('city_id')->having('COUNT(*) >10')->getSql());

        $this->assertEquals('SELECT COUNT(*), city_id FROM [country] GROUP BY [city_id] HAVING COUNT(*) >10',
            (new Query())->select('COUNT(*), city_id')->from('country')->groupBy('city_id')->having(['COUNT(*) >10'])->getSql());

        $this->assertEquals('SELECT COUNT(*), city_id FROM [country] GROUP BY [city_id] HAVING (COUNT(*) >10) AND (city_id >10)',
            (new Query())->select('COUNT(*), city_id')->from('country')->groupBy('city_id')->having(['COUNT(*) >10', 'city_id >10'])->getSql());
    }

    public function test_groupBy()
    {
        $this->assertEquals('SELECT * FROM [city] GROUP BY [city_id]',
            (new Query())->from('city')->groupBy('city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY DATE(create_time)',
            (new Query())->from('city')->groupBy('DATE(create_time)')->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY [city_id], [city_name]',
            (new Query())->from('city')->groupBy('city_id, city_name')->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY [city_id]',
            (new Query())->from('city')->groupBy(['city_id'])->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY [city_id], [city_name]',
            (new Query())->from('city')->groupBy(['city_id', 'city_name'])->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY DATE(create_time)',
            (new Query())->from('city')->groupBy(['DATE(create_time)'])->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY [c].[city_id]',
            (new Query())->from('city')->groupBy('c.city_id')->getSql());

        $this->assertEquals('SELECT * FROM [city] GROUP BY [c].[city_id]',
            (new Query())->from('city')->groupBy(['c.city_id'])->getSql());
    }

    public function test_exists()
    {
        $this->assertTrue((new Query)->from('city')->exists());
        $this->assertTrue((new Query)->from('city')->where('city_id', 1)->exists());
        $this->assertFalse((new Query)->from('city')->where('city_id', 0)->exists());
    }

    public function test_aggregate()
    {
        $this->assertEquals('SELECT COUNT(*) AS [city_count] FROM [city]',
            (new Query())->aggregate(['city_count' => 'COUNT(*)'])->from('city')->getSql());

        $this->assertEquals('SELECT COUNT([city_id]) AS [city_count] FROM [city]',
            (new Query())->aggregate(['city_count' => 'count(city_id)'])->from('city')->getSql());
    }
}