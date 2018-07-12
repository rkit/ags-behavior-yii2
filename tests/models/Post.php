<?php

namespace tests\models;

use rkit\tags\behavior\TagsBehavior;

class Post extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'tagsBehavior' => [
                'class' => TagsBehavior::class,
                'relation' => 'tags',
                'tagAttribute' => 'name',
                'tagFrequencyAttribute' => 'frequency',
                'findTag' => function ($value) {
                    return Tag::find()->where([$this->tagAttribute => $value])->one();
                },
                'createTag' => function ($value) {
                    $tag = new Tag();
                    $tag->{$this->tagAttribute} = $value;
                    return $tag;
                },
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this
            ->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('{{%post_to_tag}}', ['post_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new \yii\db\ActiveQuery(get_called_class());
    }
}
