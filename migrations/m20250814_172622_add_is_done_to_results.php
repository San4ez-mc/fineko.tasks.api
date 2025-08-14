<?php

use yii\db\Migration;

class m20250814_172622_add_is_done_to_results extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%result}}', 'is_done', $this->tinyInteger()->notNull()->defaultValue(0)->after('completed_at'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%result}}', 'is_done');
    }
}
