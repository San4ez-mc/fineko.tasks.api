<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%instruction}}`.
 */
class m250902_000000_create_instruction_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%instruction}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_instruction_organization',
            '{{%instruction}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_instruction_organization', '{{%instruction}}');
        $this->dropTable('{{%instruction}}');
    }
}
