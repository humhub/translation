<?php

namespace humhub\modules\translation\controllers;

use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\stream\StreamAction;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\translation\widgets\TranslationFormWidget;
use yii\web\HttpException;
use Yii;

class StreamController extends ContentContainerController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'stream' => [
                'class' => StreamAction::class,
                'contentContainer' => $this->contentContainer,
            ],
        ];
    }

    /**
     * Inits the Translate Controller
     *
     * @return string
     * @throws HttpException
     */
    public function actionIndex()
    {
        $model = new TranslationForm();
        $model->load(Yii::$app->request->get());

        if (!$model->validate()) {
            throw new HttpException(404);
        }

        return (Yii::$app->request->isAjax && !Yii::$app->request->isPjax)
            ? TranslationFormWidget::widget(['model' => $model])
            : $this->render('index', [
                'model' => $model,
            ]);
    }
}
