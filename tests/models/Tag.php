<?php

namespace tests\models;

class Tag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag}}';
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new \yii\db\ActiveQuery(get_called_class());
    }
}
