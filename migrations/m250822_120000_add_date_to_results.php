<?php

use yii\db\Migration;

class m250822_120000_add_date_to_results extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%result}}', 'date', $this->date()->after('expected_result'));
        $this->createIndex('idx-result-date', '{{%result}}', 'date');
        // Copy existing values from old deadline column if it exists
        if ($this->db->getTableSchema('{{%result}}')->getColumn('deadline') !== null) {
            $this->execute('UPDATE {{%result}} SET date = deadline');
            $this->dropColumn('{{%result}}', 'deadline');
        }
    }

    public function safeDown()
    {
        // restore old deadline column if needed
        if ($this->db->getTableSchema('{{%result}}')->getColumn('deadline') === null) {
            $this->addColumn('{{%result}}', 'deadline', $this->date()->after('expected_result'));
            $this->execute('UPDATE {{%result}} SET deadline = date');
        }
        $this->dropIndex('idx-result-date', '{{%result}}');
        $this->dropColumn('{{%result}}', 'date');
    }
}
