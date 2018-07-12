<?php

namespace tests;

use Yii;

trait DatabaseCaseTrait
{
    /**
     * @return PHPUnit\DbUnit\Database\Connection
     */
    public function getConnection()
    {
        Yii::$app->getDb()->open();
        return $this->createDefaultDBConnection(Yii::$app->getDb()->pdo);
    }
}
