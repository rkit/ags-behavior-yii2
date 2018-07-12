<?php

namespace tests;

use Yii;
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use tests\DatabaseCaseTrait;
use tests\models\{Post, Tag}; // phpcs:ignore

class TagsBehaviorTest extends TestCase
{
    use TestCaseTrait;
    use DatabaseCaseTrait;
    
    private $model;

    /**
     * @return PHPUnit\DbUnit\DataSet\IDataSet
     */
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(Yii::getAlias('@tests/fixtures/data.xml'));
    }

    public function testSetTags()
    {
        $model = new Post();
        $model->setTagValues(['example1', 'example2']);
        $model->save();

        $this->assertEquals($model->getTagValues(), ['example1', 'example2']);
    }

    public function testUpdateTags()
    {
        $model = new Post();
        $model->setTagValues(['example1', 'example2']);
        $model->save();

        $this->assertEquals($model->getTagValues(), ['example1', 'example2']);

        $model->setTagValues(['example1']);
        $model->save();
    
        $this->assertEquals($model->getTagValues(), ['example1']);
    }

    public function testUpdateFrequency()
    {
        $model = new Post();
        $model->setTagValues(['example1', 'example2']);
        $model->save();

        $this->assertEquals($model->getTagValues(), ['example1', 'example2']);
        
        foreach ($model->tags as $tag) {
            $this->assertEquals($tag->frequency, 1);
        }

        $model = new Post();
        $model->setTagValues(['example2']);
        $model->save();

        $query = Tag::find()->select('frequency');
        $this->assertEquals($query->where(['name' => 'example1'])->scalar(), 1);
        $this->assertEquals($query->where(['name' => 'example2'])->scalar(), 2);
    }

    public function testRemoveTags()
    {
        $model = new Post();
        $model->setTagValues(['example1', 'example2']);
        $model->save();

        $this->assertEquals($model->getTagValues(), ['example1', 'example2']);

        $model->setTagValues([]);
        $model->save();

        $this->assertEquals($model->getTagValues(), []);
    }

    public function testWithoutFrequency()
    {
        $model = new Post();

        $behaviors = $model->behaviors();
        $behaviors['tagsBehavior']['tagFrequencyAttribute'] = false;

        $model->attachBehavior('tagsBehavior', $behaviors['tagsBehavior']);
        $model->setTagValues(['example1']);
        $model->save();

        $this->assertEquals($model->getTagValues(), ['example1']);
        
        foreach ($model->tags as $tag) {
            $this->assertEquals($tag->frequency, 0);
        }

        $query = Tag::find()->select('frequency');
        $this->assertEquals($query->where(['name' => 'example1'])->scalar(), 0);
    }
}
