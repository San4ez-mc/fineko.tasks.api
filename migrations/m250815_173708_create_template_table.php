<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%template}}`.
 */
class m250815_173708_create_template_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%template}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'flags' => $this->json()->notNull(),
            'payload' => $this->json()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_template_organization',
            '{{%template}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_template_organization', '{{%template}}');
        $this->dropTable('{{%template}}');
    }
}
