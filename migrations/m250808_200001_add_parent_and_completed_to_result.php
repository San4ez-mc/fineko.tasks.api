<?php

use yii\db\Migration;

class m250808_200001_add_parent_and_completed_to_result extends Migration
{
    public function safeUp()
    {
        // parent_id (для підрезультатів)
        $this->addColumn('{{%result}}', 'parent_id', $this->integer()->null()->after('organization_id'));
        $this->createIndex('idx-result-parent_id', '{{%result}}', 'parent_id');
        $this->addForeignKey(
            'fk-result-parent_id',
            '{{%result}}',
            'parent_id',
            '{{%result}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // completed_at (INT як у твоїх created_at/updated_at)
        $this->addColumn('{{%result}}', 'completed_at', $this->integer()->null()->after('deadline'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%result}}', 'completed_at');
        $this->dropForeignKey('fk-result-parent_id', '{{%result}}');
        $this->dropIndex('idx-result-parent_id', '{{%result}}');
        $this->dropColumn('{{%result}}', 'parent_id');
    }
}
