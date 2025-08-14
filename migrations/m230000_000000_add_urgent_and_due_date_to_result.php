<?php

use yii\db\Migration;

class m230000_000000_add_urgent_and_due_date_to_result extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%result}}', 'urgent', $this->tinyInteger()->notNull()->defaultValue(0)->after('expected_result'));
        $this->addColumn('{{%result}}', 'due_date', $this->dateTime()->null()->after('deadline'));
        $this->createIndex('idx-result-due_date', '{{%result}}', 'due_date');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-result-due_date', '{{%result}}');
        $this->dropColumn('{{%result}}', 'due_date');
        $this->dropColumn('{{%result}}', 'urgent');
    }
}
