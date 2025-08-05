<?php

use yii\db\Migration;

class m250731_000400_seed_fineko_data extends Migration
{
    public function safeUp()
    {
        // organization
        $this->insert('{{%organization}}', [
            'name' => 'FINEKO',
            'comment' => 'Demo organization',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $orgId = $this->db->getLastInsertID();

        // user
        $password = Yii::$app->security->generatePasswordHash('password');
        $authKey = Yii::$app->security->generateRandomString();
        $this->insert('{{%user}}', [
            'organization_id' => $orgId,
            'username' => 'demo',
            'password_hash' => $password,
            'auth_key' => $authKey,
            'first_name' => 'Demo',
            'last_name' => 'User',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $userId = $this->db->getLastInsertID();

        $taskCount = 0;
        for ($i = 1; $i <= 10; $i++) {
            $this->insert('{{%result}}', [
                'organization_id' => $orgId,
                'title' => 'Result ' . $i,
                'description' => 'Description ' . $i,
                'expected_result' => 'Expected ' . $i,
                'deadline' => date('Y-m-d', strtotime("+{$i} days")),
                'created_by' => $userId,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $resultId = $this->db->getLastInsertID();

            for ($t = 0; $t < 3; $t++) {
                $dayOffset = $taskCount % 11; // distribute over today + 10 days
                $taskCount++;
                $this->insert('{{%task}}', [
                    'organization_id' => $orgId,
                    'result_id' => $resultId,
                    'title' => 'Task ' . $taskCount,
                    'expected_result' => 'Do work',
                    'result' => null,
                    'type' => 'normal',
                    'assigned_by' => $userId,
                    'assigned_to' => $userId,
                    'expected_time' => 60,
                    'actual_time' => null,
                    'planned_date' => date('Y-m-d', strtotime("+{$dayOffset} days")),
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
        $this->delete('{{%user}}', ['username' => 'demo']);
        $this->delete('{{%organization}}', ['name' => 'FINEKO']);
    }
}
