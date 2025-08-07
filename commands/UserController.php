<?php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\User;
use Yii;

/**
 * Console controller for user management.
 */
class UserController extends Controller
{
    /**
     * Creates a new user.
     * Prompts for username, password and email; other fields are left empty or set to defaults.
     *
     * @return int Exit code
     */
    public function actionCreate()
    {
        $username = $this->prompt('Username:');
        $password = $this->prompt('Password:');
        $email = $this->prompt('Email:');

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->organization_id = 1;
        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save()) {
            $this->stdout("User '{$username}' created successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        foreach ($user->getFirstErrors() as $attribute => $error) {
            $this->stderr("$attribute: $error\n", Console::FG_RED);
        }
        return ExitCode::UNSPECIFIED_ERROR;
    }
}
