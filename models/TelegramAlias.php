<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int|null $telegram_user_id
 * @property string|null $username
 * @property string|null $display_name
 * @property array|null $synonyms
 * @property bool $is_active
 * @property int $created_at
 */
class TelegramAlias extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'telegram_aliases';
    }
}
