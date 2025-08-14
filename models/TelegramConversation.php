<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $thread_key
 * @property int $company_id
 * @property array|null $state
 * @property int|null $expires_at
 * @property int $updated_at
 */
class TelegramConversation extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'telegram_conversations';
    }
}
