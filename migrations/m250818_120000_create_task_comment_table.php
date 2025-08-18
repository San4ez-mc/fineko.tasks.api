<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_comment}}`.
 * Has foreign keys to the tables:
 *
 * - `task`
 * - `user` (optional, якщо є таблиця users)
 */
class m250818_120000_create_task_comment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%task_comment}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->null(),
            'author_name' => $this->string(255)->null(), // fallback, якщо немає author_id у фронті
            'text' => $this->text()->notNull(),
            'parent_id' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // індекси
        $this->createIndex('idx-task_comment-task_id', '{{%task_comment}}', 'task_id');
        $this->createIndex('idx-task_comment-parent_id', '{{%task_comment}}', 'parent_id');
        $this->createIndex('idx-task_comment-author_id', '{{%task_comment}}', 'author_id');

        // зв’язки
        $this->addForeignKey(
            'fk-task_comment-task_id',
            '{{%task_comment}}',
            'task_id',
            '{{%task}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Якщо у тебе таблиця користувачів називається інакше — поправ тут
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->addForeignKey(
                'fk-task_comment-author_id',
                '{{%task_comment}}',
                'author_id',
                '{{%user}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }

        // self-reference replies
        $this->addForeignKey(
            'fk-task_comment-parent_id',
            '{{%task_comment}}',
            'parent_id',
            '{{%task_comment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-task_comment-parent_id', '{{%task_comment}}');

        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->dropForeignKey('fk-task_comment-author_id', '{{%task_comment}}');
        }

        $this->dropForeignKey('fk-task_comment-task_id', '{{%task_comment}}');
        $this->dropTable('{{%task_comment}}');
    }
}
