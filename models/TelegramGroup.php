<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $company_id
 * @property int $chat_id
 * @property string $title
 * @property int|null $added_by_user_id
 * @property bool $is_active
 * @property int $created_at
 */
class TelegramGroup extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'telegram_groups';
    }
}
