<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%result}}`.
 */
class m250717_235154_create_result_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%result}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'expected_result' => $this->text(),
            'deadline' => $this->date(),
            'created_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_result_organization',
            '{{%result}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_result_created_by',
            '{{%result}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_result_created_by', '{{%result}}');
        $this->dropForeignKey('fk_result_organization', '{{%result}}');
        $this->dropTable('{{%result}}');
    }
}
