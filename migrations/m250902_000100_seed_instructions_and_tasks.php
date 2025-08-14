<?php

use yii\db\Migration;

/**
 * Seeds initial instructions and tasks.
 */
class m250902_000100_seed_instructions_and_tasks extends Migration
{
    public function safeUp()
    {
        $time = time();

        // instructions
        $this->insert('{{%instruction}}', [
            'organization_id' => 1,
            'title' => 'Getting Started',
            'content' => 'Use this app to manage your tasks.',
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $this->insert('{{%instruction}}', [
            'organization_id' => 1,
            'title' => 'Reporting',
            'content' => 'Report progress daily via the app.',
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        // tasks
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $afterTomorrow = date('Y-m-d', strtotime('+2 days'));
        $nextWeek = date('Y-m-d', strtotime('+7 days'));

        $this->insert('{{%task}}', [
            'organization_id' => 1,
            'result_id' => null,
            'title' => 'Task for today',
            'expected_result' => "Complete today's task",
            'result' => null,
            'type' => 'normal',
            'assigned_by' => 1,
            'assigned_to' => 1,
            'expected_time' => 60,
            'actual_time' => null,
            'planned_date' => $today,
            'status' => 'new',
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $this->insert('{{%task}}', [
            'organization_id' => 1,
            'result_id' => null,
            'title' => 'Task for tomorrow',
            'expected_result' => "Complete tomorrow's task",
            'result' => null,
            'type' => 'normal',
            'assigned_by' => 1,
            'assigned_to' => 1,
            'expected_time' => 60,
            'actual_time' => null,
            'planned_date' => $tomorrow,
            'status' => 'new',
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $this->insert('{{%task}}', [
            'organization_id' => 1,
            'result_id' => null,
            'title' => 'Task for day after tomorrow',
            'expected_result' => "Complete day after tomorrow's task",
            'result' => null,
            'type' => 'normal',
            'assigned_by' => 1,
            'assigned_to' => 1,
            'expected_time' => 60,
            'actual_time' => null,
            'planned_date' => $afterTomorrow,
            'status' => 'new',
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $this->insert('{{%task}}', [
            'organization_id' => 1,
            'result_id' => null,
            'title' => 'Task for next week',
            'expected_result' => 'Complete next week\'s task',
            'result' => null,
            'type' => 'normal',
            'assigned_by' => 1,
            'assigned_to' => 1,
            'expected_time' => 60,
            'actual_time' => null,
            'planned_date' => $nextWeek,
            'status' => 'new',
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%task}}', ['title' => [
            'Task for today',
            'Task for tomorrow',
            'Task for day after tomorrow',
            'Task for next week',
        ]]);

        $this->delete('{{%instruction}}', ['title' => [
            'Getting Started',
            'Reporting',
        ]]);
    }
}
