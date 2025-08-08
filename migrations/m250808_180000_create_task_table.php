<?php

use yii\db\Migration;

class m250808_180000_create_task_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer(),
        ]);

        $this->createIndex('idx-task-user_id', '{{%task}}', 'user_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%task}}');
    }
}
