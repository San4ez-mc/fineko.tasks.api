<?php
use yii\db\Migration;

class m250718_000200_add_manager_to_position_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%position}}', 'manager_id', $this->integer());
        $this->addForeignKey(
            'fk_position_manager',
            '{{%position}}',
            'manager_id',
            '{{%position}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_position_manager', '{{%position}}');
        $this->dropColumn('{{%position}}', 'manager_id');
    }
}
