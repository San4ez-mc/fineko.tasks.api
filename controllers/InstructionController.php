<?php
namespace app\controllers;

use Yii;

/**
 * Віддає інструкції для фронтенду.
 */
class InstructionController extends ApiController
{
    /**
     * Дії, які доступні без Bearer-автентифікації.
     */
    protected function authExcept(): array
    {
        return array_merge(parent::authExcept(), ['index']);
    }

    public function verbs()
    {
        return array_merge(parent::verbs(), [
            'index' => ['GET'],
        ]);
    }

    /**
     * GET /instructions
     * Повертає вміст файлу AI_RULES.md
     */
    public function actionIndex()
    {
        $file = Yii::getAlias('@app/AI_RULES.md');
        if (!is_file($file)) {
            Yii::$app->response->statusCode = 404;
            return ['error' => 'Instructions file not found'];
        }
        return ['content' => file_get_contents($file)];
    }
}
