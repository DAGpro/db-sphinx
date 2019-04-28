<?php

namespace yiiunit\sphinx;

use Yiisoft\Cache\FileCache;

/**
 * @group sphinx
 */
class SchemaTest extends TestCase
{
    public function testFindIndexNames()
    {
        $schema = $this->getConnection()->getSchema();

        $indexes = $schema->getIndexNames();
        $this->assertContains('yii2_test_article_index', $indexes);
        $this->assertContains('yii2_test_item_index', $indexes);
        $this->assertContains('yii2_test_rt_index', $indexes);
    }

    public function testGetIndexSchemas()
    {
        $schema = $this->getConnection()->getSchema();

        $indexes = $schema->getIndexSchemas();
        $this->assertEquals(count($schema->getIndexNames()), count($indexes));
        foreach ($indexes as $index) {
            $this->assertInstanceOf('yii\sphinx\IndexSchema', $index);
        }
    }

    public function testGetNonExistingIndexSchema()
    {
        $this->assertNull($this->getConnection()->getSchema()->getIndexSchema('non_existing_index'));
    }

    public function testSchemaRefresh()
    {
        $schema = $this->getConnection()->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();
        $noCacheIndex = $schema->getIndexSchema('yii2_test_rt_index', true);
        $cachedIndex = $schema->getIndexSchema('yii2_test_rt_index', true);
        $this->assertEquals($noCacheIndex, $cachedIndex);
    }

    public function testGetPDOType()
    {
        $values = [
            [null, \PDO::PARAM_NULL],
            ['', \PDO::PARAM_STR],
            ['hello', \PDO::PARAM_STR],
            [0, \PDO::PARAM_INT],
            [1, \PDO::PARAM_INT],
            [1337, \PDO::PARAM_INT],
            [true, \PDO::PARAM_BOOL],
            [false, \PDO::PARAM_BOOL],
            [$fp = fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
        ];

        $schema = $this->getConnection()->getSchema();

        foreach ($values as $value) {
            $this->assertEquals($value[1], $schema->getPdoType($value[0]));
        }
        fclose($fp);
    }

    public function testIndexType()
    {
        $schema = $this->getConnection()->getSchema();

        $index = $schema->getIndexSchema('yii2_test_article_index');
        $this->assertEquals('local', $index->type);
        $this->assertFalse($index->isRt);

        $index = $schema->getIndexSchema('yii2_test_rt_index');
        $this->assertEquals('rt', $index->type);
        $this->assertTrue($index->isRt);
    }

    /**
     * @see https://github.com/yiisoft/yii2-sphinx/issues/45
     */
    public function testGetSchemaPrimaryKey()
    {
        /* @var $indexSchema \yii\sphinx\IndexSchema */
        $indexSchema = $this->getConnection()->getSchema()->getIndexSchema('yii2_test_item_index');
        $this->assertEquals('id', $indexSchema->primaryKey);

        $indexSchema = $this->getConnection()->getSchema()->getIndexSchema('yii2_test_rt_index');
        $this->assertEquals('id', $indexSchema->primaryKey);

        $indexSchema = $this->getConnection()->getSchema()->getIndexSchema('yii2_test_distributed');
        $this->assertEquals('id', $indexSchema->primaryKey);
    }
}
