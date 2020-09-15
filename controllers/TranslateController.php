<?php

namespace humhub\modules\translation\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\access\StrictAccess;
use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\permissions\ManageTranslations;
use humhub\modules\translation\models\TranslationLog;
use humhub\modules\translation\widgets\TranslationFormWidget;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

class TranslateController extends \humhub\components\Controller
{
    /**
     * @inheritDoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY]
        ];
    }

    /**
     * Current active language code e.g. en
     *
     * @var string
     */
    public $language;

    /**
     * Current active module e.g. Core
     *
     * @var string
     */
    public $moduleId;

    /**
     * Language file inside language / module
     *
     * @var string
     */
    public $file;

    /**
     * FileName of message file
     *
     * @var string
     */
    public $messageFileName = "";

    /**
     * Current loaded message file for moduleId/file/language combination
     *
     * @var string
     */
    public $messages;

    /**
     * @inheritDoc
     */
    public $access = StrictAccess::class;

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (empty(Languages::isLanguageAvailable())) {
            throw new HttpException(404, 'No language available!');
        }

        return parent::beforeAction($action);
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
        $model->validate();

        return (Yii::$app->request->isAjax && !Yii::$app->request->isPjax)
            ? TranslationFormWidget::widget(['model' => $model])
            : $this->render('index', [
                'model' => $model,
            ]);
    }

    public function actionSave()
    {
        $model = new TranslationForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if(empty($model->warnings) && empty($model->errors)) {
                $this->view->saved();
            } else if(!empty($model->errors)) {
                $this->view->error(Yii::t('TranslationModule.base', 'Some translations could not be saved.'));
            } else {
                $this->view->warn(Yii::t('TranslationModule.base', 'Some translations may have been purified.'));
            }
        }

        // In case there is no related language space
        if(!empty($model->getFirstError('space'))) {
            $this->view->error($model->getFirstError('space'));
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionHistory($language, $moduleId, $file, $message)
    {
        $basePath = BasePath::getBasePath($moduleId);

        if(!$basePath->validate() || ! $basePath->validateLanguagePath($language)) {
            throw new HttpException(404);
        }

        $messageFile = $basePath->getMessageFile($file);

        if(!$messageFile->validate() || ! $messageFile->validateLanguagePath($language)) {
            throw new HttpException(404);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => TranslationLog::findHistory($messageFile, $language, $message)
        ]);

        return $this->render('history', [
            'messageFile' => $messageFile,
            'language' => $language,
            'message' => $message,
            'dataProvider' => $dataProvider
        ]);

    }
}
