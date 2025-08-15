<?php

namespace app\controllers;

use app\models\Template;
use Yii;
use yii\filters\VerbFilter;

class TemplateController extends ApiController
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'create' => ['POST'],
            ],
        ];
        return $b;
    }

    public function actionIndex()
    {
        $orgId = (int) (Yii::$app->user->identity->organization_id ?? 0);
        $items = Template::find()->where(['organization_id' => $orgId])->orderBy(['id' => SORT_DESC])->all();
        return [
            'items' => array_map(fn(Template $m) => $m->toArray(), $items),
        ];
    }

    public function actionCreate()
    {
        $m = new Template();
        $m->load(Yii::$app->request->post(), '');
        if ($m->save()) {
            Yii::$app->response->statusCode = 201;
            return $m->toArray();
        }
        Yii::$app->response->statusCode = 422;
        return ['errors' => $m->getErrors()];
    }
}
