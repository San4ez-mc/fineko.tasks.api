<?php

use yii\db\Migration;

/**
 * Handles the creation of tables related to Telegram integration.
 */
class m250901_000000_create_telegram_tables extends Migration
{
    public function safeUp()
    {
        // telegram_groups
        $this->createTable('telegram_groups', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'chat_id' => $this->bigInteger()->notNull(),
            'title' => $this->string()->notNull(),
            'added_by_user_id' => $this->integer(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-telegram_groups-chat_id', 'telegram_groups', 'chat_id');
        $this->createIndex('idx-telegram_groups-company_id', 'telegram_groups', 'company_id');

        // telegram_aliases
        $this->createTable('telegram_aliases', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'telegram_user_id' => $this->bigInteger(),
            'username' => $this->string(),
            'display_name' => $this->string(),
            'synonyms' => $this->json(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-telegram_aliases-company_id', 'telegram_aliases', 'company_id');
        $this->createIndex('idx-telegram_aliases-telegram_user_id', 'telegram_aliases', 'telegram_user_id');

        // telegram_conversations
        $this->createTable('telegram_conversations', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->bigInteger()->notNull(),
            'thread_key' => $this->bigInteger()->notNull(),
            'company_id' => $this->integer()->notNull(),
            'state' => $this->json(),
            'expires_at' => $this->integer(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-telegram_conversations-chat_id', 'telegram_conversations', 'chat_id');
        $this->createIndex('idx-telegram_conversations-company_id', 'telegram_conversations', 'company_id');

        // telegram_messages_log
        $this->createTable('telegram_messages_log', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->bigInteger()->notNull(),
            'from_user_id' => $this->bigInteger(),
            'message_id' => $this->bigInteger()->notNull(),
            'payload' => $this->json(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-telegram_messages_log-chat_id', 'telegram_messages_log', 'chat_id');
    }

    public function safeDown()
    {
        $this->dropTable('telegram_messages_log');
        $this->dropTable('telegram_conversations');
        $this->dropTable('telegram_aliases');
        $this->dropTable('telegram_groups');
    }
}
