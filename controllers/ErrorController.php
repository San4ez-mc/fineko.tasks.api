<?php
namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;

class ErrorController extends Controller
{
    public function actionIndex()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return [
                'name' => $exception->getName(),
                'message' => $exception->getMessage(),
                'code' => $exception->statusCode ?? $exception->getCode(),
            ];
        }
        return ['message' => 'An unknown error occurred.'];
    }
}
