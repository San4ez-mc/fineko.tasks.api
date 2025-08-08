<?php

use yii\db\Migration;

class m250808_170000_add_tokens_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'access_token', $this->string(255)->null()->after('auth_key'));
        $this->addColumn('{{%user}}', 'access_token_expire', $this->integer()->null()->after('access_token'));
        $this->addColumn('{{%user}}', 'refresh_token', $this->string(255)->null()->after('access_token_expire'));
        $this->addColumn('{{%user}}', 'refresh_token_expire', $this->integer()->null()->after('refresh_token'));

        $this->createIndex('idx-user-refresh_token', '{{%user}}', 'refresh_token', false);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-user-refresh_token', '{{%user}}');
        $this->dropColumn('{{%user}}', 'refresh_token_expire');
        $this->dropColumn('{{%user}}', 'refresh_token');
        $this->dropColumn('{{%user}}', 'access_token_expire');
        $this->dropColumn('{{%user}}', 'access_token');
    }
}
