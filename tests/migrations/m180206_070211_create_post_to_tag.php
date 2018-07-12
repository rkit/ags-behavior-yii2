<?php

class m180206_070211_create_post_to_tag extends \yii\db\Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%post_to_tag}}', [
            'post_id' => $this->integer()->notNull()->defaultValue(0),
            'tag_id' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addPrimaryKey('', '{{%post_to_tag}}', ['post_id', 'tag_id']);

        $this->addForeignKey(
            'fk_post_to_tag__post_id__post_id',
            '{{%post_to_tag}}',
            'post_id',
            '{{%post}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_post_to_tag__tag_id__tag_id',
            '{{%post_to_tag}}',
            'tag_id',
            '{{%tag}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%post_to_tag}}');
    }
}
