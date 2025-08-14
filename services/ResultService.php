<?php

namespace app\services;

use app\models\Result;
use Yii;

class ResultService
{
    /**
     * Creates a Result from Telegram parsed data.
     *
     * @param array $data
     * @return Result|null
     */
    public function createFromTelegram(array $data): ?Result
    {
        $m = new Result();
        $m->name = $data['title'] ?? 'Untitled';
        $m->description = $data['description'] ?? '';
        if (isset($data['assignee_id'])) {
            $m->assigned_to = (int) $data['assignee_id'];
        }
        if (isset($data['due_date'])) {
            $ts = strtotime($data['due_date']);
            if ($ts !== false) {
                $m->due_date = $ts;
            }
        }
        $m->urgent = (bool) ($data['urgent'] ?? false);
        $m->priority = $data['priority'] ?? null;
        $m->created_at = time();
        $m->updated_at = time();
        $m->setter_id = Yii::$app->user->id ?? null;

        if ($m->save()) {
            return $m;
        }
        Yii::error('Failed to save result: ' . json_encode($m->errors), 'application');
        return null;
    }
}
