<?php

namespace humhub\modules\translation\controllers;

use Yii;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\web\HttpException;
use yii\helpers\Url;

class TranslateController extends \humhub\components\Controller
{

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

    public function beforeAction($action)
    {

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        /**
         * Load language
         */
        $languages = $this->module->getLanguages();
        if (count($languages) == 0) {
            throw new HttpException(404, 'No language available!');
        }
        $this->language = Yii::$app->request->get('language', array_values($languages)[0]);
        if (!in_array($this->language, $languages)) {
            throw new HttpException(404, 'Language not found!');
        }

        /**
         * Load given Module Class
         */
        $this->moduleId = Yii::$app->request->get('moduleId', 'core');
        if (!in_array($this->moduleId, array_keys($this->module->getModuleIds()))) {
            throw new HttpException(404, 'Module not found!');
        }
        /**
         * Load given File
         */
        $files = $this->module->getFiles($this->moduleId, $this->language);
        if (count($files) == 0) {
            throw new HttpException(400, 'Files not found!');
        }

        $this->file = Yii::$app->request->get('file', '');
        if (!in_array($this->file, $this->module->getFiles($this->moduleId, $this->language))) {
            $this->file = array_values($files)[0];
        }

        /**
         * Get Messages
         */
        $this->messages = [];
        $this->messageFileName = $this->module->getTranslationFile($this->moduleId, $this->language, $this->file);
        if (file_exists($this->messageFileName)) {
            $this->messages = $this->module->getTranslationMessages($this->messageFileName);
        }

        /**
         * Save Messages
         */

        if(Yii::$app->request->isPjax){
            if (Yii::$app->request->post('saveForm') == 1) {
                if (count($this->messages) != 0) {
                    foreach ($this->messages as $orginalMessage => $oldTranslation) {
                        $newTranslation = Yii::$app->request->post('tid_' . md5($orginalMessage));
                        $newTranslation = trim($newTranslation);

                        $newTranslationPure = HtmlPurifier::process($newTranslation);

                        if(empty($newTranslation)) {
                            $this->view->error(Yii::t('TranslationModule.base', 'Your translation seems to be empty and therefore could not be saved.'));
                        } else if($newTranslation !== $newTranslationPure) {
                            Yii::error('Suspicious translation detected by user: '.Yii::$app->user->getId().' file: '. $this->file . ' '.$newTranslation);
                            $this->view->warn(Yii::t('TranslationModule.base', 'Your input has been purified from suspicious html.'));
                        } else {
                            $this->view->saved();
                        }

                        $validationResult = $this->validateTranslation($orginalMessage, $newTranslationPure);

                        if (!empty($newTranslationPure) && $validationResult === true) {
                            $this->messages[$orginalMessage] = $newTranslationPure;

                        } else if($validationResult !== true) {
                            $this->view->error($validationResult);
                        } else {
                            $this->view->error(Yii::t('TranslationModule.base', 'Could not save empty translation.'));
                        }
                    }

                    $this->module->saveTranslationMessages($this->messageFileName, $this->messages);
                }
            }
        }
        return parent::beforeAction($action);
    }

    private function validateTranslation($original, $translation)
    {
        $matches = [];
        $params = [];
        preg_match_all('/{([a-zA-Z]+),([a-zA-Z]+),/m', $translation, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $param = $match[1];
            $function = $match[2];

            switch ($function) {
                case 'date':
                case 'time':
                    $params[$param] = time();
                    break;
                default:
                    $params[$param] = 4;
                    break;

            }
        }

        $matches = [];

        preg_match_all('/{([a-zA-Z]+)}/m', $translation, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if(strpos($original, $match[0]) === false) {
                return Yii::t('TranslationModule.base', 'The translation contains an invalid parameter {match}', ['match' => $match[0]]);
            }
            $params[$match[1]] = 'Test Value';
        }

        $formatter = Yii::$app->getI18n()->getMessageFormatter();
        $formatter->format($translation, $params, $this->language);
        if($formatter->getErrorMessage()) {
            return Yii::t('TranslationModule.base', 'Invalid translation pattern detected, please see {link}', ['error' => $formatter->getErrorMessage(), 'link' =>'https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#message-formatting']);
        }

        return true;
    }

    /**
     * Inits the Translate Controller
     *
     * @return string
     */

    public function actionIndex()
    {

        $moduleIds = $this->module->getModuleIds();
        array_walk($moduleIds, function(&$value, $key) {
            $value .= " (" . $this->module->getModulePercentage($key, $this->language) . "%)";
        });

        $files = $this->module->getFiles($this->moduleId, $this->language);
        array_walk($files, function(&$value, $key) {
            $value .= " (" . $this->module->getFilePercentage($key, $this->moduleId, $this->language) . "%)";
        });

        $languages = $this->module->getLanguages();
        array_walk($languages, function(&$value, $key) {
            if ($key == $this->language) {
                $value .= " (" . $this->module->getLanguagePercentage($key) . "%)";
            }
        });

// Render Template
        return $this->render('index', [
// Available Options
            'moduleIds' => $moduleIds,
            'languages' => $languages,
            'files' => $files,
            // Current selection
            'language' => $this->language,
            'moduleId' => $this->moduleId,
            'file' => $this->file,
            // Translation
            'messages' => $this->messages
        ]);
    }
}
