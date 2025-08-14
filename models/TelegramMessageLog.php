<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $chat_id
 * @property int|null $from_user_id
 * @property int $message_id
 * @property array|null $payload
 * @property int $created_at
 */
class TelegramMessageLog extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'telegram_messages_log';
    }
}
