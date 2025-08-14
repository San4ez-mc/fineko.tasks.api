<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%business_process}}`.
 */
class m250814_000400_create_business_process_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%business_process}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'schema' => $this->text()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%business_process}}');
    }
}
