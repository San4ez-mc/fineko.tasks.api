<?php
use yii\db\Migration;

class m250718_000000_create_position_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%position}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_position_organization',
            '{{%position}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_position_organization', '{{%position}}');
        $this->dropTable('{{%position}}');
    }
}
