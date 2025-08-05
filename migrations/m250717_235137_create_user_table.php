<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m250717_235137_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'username' => $this->string(255)->notNull()->unique(),
            'email' => $this->string(255)->unique(),
            'password_hash' => $this->string(255),
            'auth_key' => $this->string(32),
            'first_name' => $this->string(100),
            'last_name' => $this->string(100),
            'telegram_id' => $this->string(50),
            'telegram_username' => $this->string(100),
            'role' => $this->string(50)->defaultValue('user'),
            'status' => $this->smallInteger()->defaultValue(10),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_user_organization',
            '{{%user}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_organization', '{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
