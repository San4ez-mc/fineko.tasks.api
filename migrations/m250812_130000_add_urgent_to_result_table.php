<?php

use yii\db\Migration;

class m250812_130000_add_urgent_to_result_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%result}}', true) === null) {
            throw new \RuntimeException('Table "result" not found.');
        }
        if ($this->db->schema->getTableSchema('{{%result}}')->getColumn('urgent') === null) {
            $this->addColumn('{{%result}}', 'urgent', $this->boolean()->notNull()->defaultValue(false)->after('expected_result'));
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%result}}', true) !== null && $this->db->schema->getTableSchema('{{%result}}')->getColumn('urgent') !== null) {
            $this->dropColumn('{{%result}}', 'urgent');
        }
    }
}
