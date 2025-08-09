<?php

use yii\db\Migration;

/**
 * Adds assigned_to and setter_id to result and task tables.
 */
class m250809_120000_add_owner_fields_to_result_and_task extends Migration
{
    public function safeUp()
    {
        // ===== result =====
        if ($this->db->schema->getTableSchema('{{%result}}', true) === null) {
            throw new \RuntimeException('Table "result" not found. Import current schema first.');
        }

        // assigned_to
        if ($this->db->schema->getTableSchema('{{%result}}')->getColumn('assigned_to') === null) {
            $this->addColumn('{{%result}}', 'assigned_to', $this->integer()->null()->after('created_by'));
            $this->createIndex('idx-result-assigned_to', '{{%result}}', 'assigned_to');
            $this->addForeignKey(
                'fk-result-assigned_to',
                '{{%result}}',
                'assigned_to',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
            // заповнюємо поточним автором, щоб фронт не отримав порожні значення
            $this->execute('UPDATE {{%result}} SET assigned_to = created_by WHERE assigned_to IS NULL');
        }

        // setter_id
        if ($this->db->schema->getTableSchema('{{%result}}')->getColumn('setter_id') === null) {
            $this->addColumn('{{%result}}', 'setter_id', $this->integer()->null()->after('assigned_to'));
            $this->createIndex('idx-result-setter_id', '{{%result}}', 'setter_id');
            $this->addForeignKey(
                'fk-result-setter_id',
                '{{%result}}',
                'setter_id',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
            // якщо не вказано, вважаємо що постановник = автор
            $this->execute('UPDATE {{%result}} SET setter_id = created_by WHERE setter_id IS NULL');
        }

        // ===== task =====
        // Примітка: таблиця повинна існувати. Якщо у вас інша назва (наприклад, "tasks" чи "todo"),
        // скажи мені — піджену міграцію. Поки що — {{%task}}.
        if ($this->db->schema->getTableSchema('{{%task}}', true) !== null) {
            if ($this->db->schema->getTableSchema('{{%task}}')->getColumn('assigned_to') === null) {
                $this->addColumn('{{%task}}', 'assigned_to', $this->integer()->null()->after('created_by'));
                $this->createIndex('idx-task-assigned_to', '{{%task}}', 'assigned_to');
                $this->addForeignKey(
                    'fk-task-assigned_to',
                    '{{%task}}',
                    'assigned_to',
                    '{{%user}}',
                    'id',
                    'SET NULL',
                    'CASCADE'
                );
                $this->execute('UPDATE {{%task}} SET assigned_to = created_by WHERE assigned_to IS NULL');
            }

            if ($this->db->schema->getTableSchema('{{%task}}')->getColumn('setter_id') === null) {
                $this->addColumn('{{%task}}', 'setter_id', $this->integer()->null()->after('assigned_to'));
                $this->createIndex('idx-task-setter_id', '{{%task}}', 'setter_id');
                $this->addForeignKey(
                    'fk-task-setter_id',
                    '{{%task}}',
                    'setter_id',
                    '{{%user}}',
                    'id',
                    'SET NULL',
                    'CASCADE'
                );
                $this->execute('UPDATE {{%task}} SET setter_id = created_by WHERE setter_id IS NULL');
            }
        }
    }

    public function safeDown()
    {
        // ===== result =====
        if ($this->db->schema->getTableSchema('{{%result}}', true) !== null) {
            if ($this->db->schema->getTableSchema('{{%result}}')->getColumn('setter_id') !== null) {
                $this->dropForeignKey('fk-result-setter_id', '{{%result}}');
                $this->dropIndex('idx-result-setter_id', '{{%result}}');
                $this->dropColumn('{{%result}}', 'setter_id');
            }
            if ($this->db->schema->getTableSchema('{{%result}}')->getColumn('assigned_to') !== null) {
                $this->dropForeignKey('fk-result-assigned_to', '{{%result}}');
                $this->dropIndex('idx-result-assigned_to', '{{%result}}');
                $this->dropColumn('{{%result}}', 'assigned_to');
            }
        }

        // ===== task =====
        if ($this->db->schema->getTableSchema('{{%task}}', true) !== null) {
            if ($this->db->schema->getTableSchema('{{%task}}')->getColumn('setter_id') !== null) {
                $this->dropForeignKey('fk-task-setter_id', '{{%task}}');
                $this->dropIndex('idx-task-setter_id', '{{%task}}');
                $this->dropColumn('{{%task}}', 'setter_id');
            }
            if ($this->db->schema->getTableSchema('{{%task}}')->getColumn('assigned_to') !== null) {
                $this->dropForeignKey('fk-task-assigned_to', '{{%task}}');
                $this->dropIndex('idx-task-assigned_to', '{{%task}}');
                $this->dropColumn('{{%task}}', 'assigned_to');
            }
        }
    }
}
