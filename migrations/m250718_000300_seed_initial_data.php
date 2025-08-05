<?php

use yii\db\Migration;

class m250718_000300_seed_initial_data extends Migration
{
    public function safeUp()
    {
        // organization
        $this->insert('{{%organization}}', [
            'id' => 1,
            'name' => 'FINEKO',
            'comment' => 'Demo organization',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // user
        $password = Yii::$app->security->generatePasswordHash('password');
        $authKey = Yii::$app->security->generateRandomString();
        $this->insert('{{%user}}', [
            'id' => 1,
            'organization_id' => 1,
            'username' => 'admin',
            'password_hash' => $password,
            'auth_key' => $authKey,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'telegram_id' => '123456',
            'telegram_username' => 'admin',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // results
        for ($i = 1; $i <= 10; $i++) {
            $this->insert('{{%result}}', [
                'id' => $i,
                'organization_id' => 1,
                'title' => "Result {$i}",
                'description' => "Description {$i}",
                'expected_result' => "Expected {$i}",
                'deadline' => date('Y-m-d', strtotime("+{$i} days")),
                'created_by' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }

        // tasks
        $taskId = 1;
        for ($r = 1; $r <= 10; $r++) {
            for ($t = 0; $t < 3; $t++) {
                $this->insert('{{%task}}', [
                    'id' => $taskId++,
                    'organization_id' => 1,
                    'result_id' => $r,
                    'title' => "Task {$taskId}",
                    'expected_result' => 'Do work',
                    'result' => null,
                    'type' => 'normal',
                    'assigned_by' => 1,
                    'assigned_to' => 1,
                    'expected_time' => 60,
                    'actual_time' => null,
                    'planned_date' => date('Y-m-d', strtotime("+{$t} days")),
                    'status' => 'new',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%task}}');
        $this->delete('{{%result}}');
        $this->delete('{{%user}}', ['id' => 1]);
        $this->delete('{{%organization}}', ['id' => 1]);
    }
}
