<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m250717_235249_create_task_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'result_id' => $this->integer(), // зв’язок з великою задачею
            'title' => $this->string(255)->notNull(),
            'expected_result' => $this->text(),
            'result' => $this->text(),
            'type' => $this->string(50), // важлива-термінова і т.д.
            'assigned_by' => $this->integer(),
            'assigned_to' => $this->integer(),
            'expected_time' => $this->integer(), // хвилини/години
            'actual_time' => $this->integer(),
            'planned_date' => $this->date(),
            'comments' => $this->text(),
            'status' => $this->string(50)->defaultValue('new'),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_task_organization',
            '{{%task}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_task_result',
            '{{%task}}',
            'result_id',
            '{{%result}}',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_task_assigned_by',
            '{{%task}}',
            'assigned_by',
            '{{%user}}',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk_task_assigned_to',
            '{{%task}}',
            'assigned_to',
            '{{%user}}',
            'id',
            'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_task_assigned_to', '{{%task}}');
        $this->dropForeignKey('fk_task_assigned_by', '{{%task}}');
        $this->dropForeignKey('fk_task_result', '{{%task}}');
        $this->dropForeignKey('fk_task_organization', '{{%task}}');
        $this->dropTable('{{%task}}');
    }
}
