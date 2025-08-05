<?php
use yii\db\Migration;

class m250718_000100_create_user_position_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_position}}', [
            'user_id' => $this->integer()->notNull(),
            'position_id' => $this->integer()->notNull(),
            'PRIMARY KEY(user_id, position_id)',
        ]);

        $this->addForeignKey(
            'fk_user_position_user',
            '{{%user_position}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_user_position_position',
            '{{%user_position}}',
            'position_id',
            '{{%position}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_position_position', '{{%user_position}}');
        $this->dropForeignKey('fk_user_position_user', '{{%user_position}}');
        $this->dropTable('{{%user_position}}');
    }
}
