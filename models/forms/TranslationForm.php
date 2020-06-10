<?php

namespace humhub\modules\translation\models\forms;

use humhub\modules\space\models\Space;
use humhub\modules\translation\permissions\ManageTranslations;
use humhub\modules\translation\models\TranslationCoverage;
use humhub\modules\translation\models\TranslationFileIF;
use Yii;
use yii\base\Model;
use humhub\modules\translation\models\BasePath;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\MessageFile;
use humhub\modules\translation\models\TranslationLog;

class TranslationForm extends Model implements TranslationFileIF
{
    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $moduleId = BasePath::CORE_MODULE_ID;

    /**
     * @var MessageFile[]
     */
    public $files;

    /**
     * @var BasePath
     */
    public $basePath;

    /**
     * @var string
     */
    public $file;

    /**
     * @var MessageFile
     */
    public $messageFile;

    /**
     * @var array
     */
    public $messages;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var array
     */
    public $warnings = [];

    /**
     * @var Space
     */
    public $space;

    public function rules()
    {
        return [
            [['language'], 'validateLanguage'],
            [['language', 'moduleId', 'file'], 'string'],
            [['language', 'moduleId', 'files', 'file', 'messageFile'], 'required'],
            [['file'], 'validateFile'],
        ];
    }

    public function validateFile()
    {
        if (!$this->messageFile || !$this->messageFile->validate()) {
            $this->addError('file', 'Message file not found!');
        }
    }

    public function validateLanguage()
    {
        if (!in_array($this->language, Languages::getTranslatableUserLanguages())) {
            $this->addError('language', 'You are not allowed to translate this language!');
        }
        if (!$this->messageFile || !$this->messageFile->validateLanguagePath($this->language)) {
            $this->addError('language', 'Language file not found!');
        }
    }

    public function attributeLabels()
    {
        return [
            'moduleId' => Yii::t('TranslationModule.base', 'Module'),
            'language' => Yii::t('TranslationModule.base', 'Language'),
            'file' => Yii::t('TranslationModule.base', 'File')
        ];
    }

    /**
     * @var TranslationLog[]
     */
    private $translationLogs = [];

    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);

        $this->language = $this->language ?: Languages::getDefaultLanguage();
        $this->basePath = BasePath::getBasePath($this->moduleId);
        $this->files = $this->basePath->getMessageFiles($this->language);

        if (!$this->file && !empty($this->files)) {
            $this->file = $this->files[0]->getBaseName();
        }

        if ($this->file) {
            $this->messageFile = $this->basePath->getMessageFile($this->file);
        }

        $this->loadMessages();

        // TODO: actually save logs in save instead of load...

        if (!$result) {
            return false;
        }

        $this->space = Languages::findSpaceByLanguage($this->language);
        if (!$this->space) {
            return false;
        }

        $this->translationLogs = [];

        foreach ($this->messages as $originalMessage => $oldTranslation) {
            $translationModel = new TranslationLog($this->space, [
                'language' => $this->language,
                'module_id' => $this->moduleId,
                'file' => $this->file,
                'translation_old' => $oldTranslation,
                'message' => $originalMessage,
            ]);

            if ($translationModel->load($data)) {
                $this->translationLogs[] = $translationModel;
            }
        }

        return $result;
    }

    protected function loadMessages()
    {
        $this->messages = [];
        if ($this->messageFile instanceof MessageFile && $this->messageFile->validate()) {
            $this->messages = $this->messageFile->getMessages($this->language);
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function save()
    {
        if(!$this->space || !$this->space->can(ManageTranslations::class)) {
            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        if (empty($this->messages)) {
            return true;
        }

        $this->saveAndValidateTranslations();

        if (!$this->basePath->updateTranslations($this->language, $this->file, $this->messages)) {
            return false;
        }

        $this->loadMessages();
        return true;
    }

    private function saveAndValidateTranslations()
    {
        foreach ($this->translationLogs as $translationModel) {
            $translationModel->save();

            if ($translationModel->hasErrors('translation')) {
                $this->errors[$translationModel->getTID()] = $translationModel->getFirstError('translation');
            }

            if ($translationModel->wasPurified) {
                $this->warnings[$translationModel->getTID()] = Yii::t('TranslationModule.base', 'Your input has been purified from suspicious html.');
            }

            if (!$translationModel->hasErrors()) {
                $this->messages[$translationModel->message] = $translationModel->translation;
            }
        }
    }

    public function getTranslationFieldClass($message)
    {
        $tid = TranslationLog::tid($message);
        if (isset($this->errors[$tid])) {
            return 'has-error';
        }

        if (isset($this->warnings[$tid])) {
            return 'has-warning';
        }

        return '';
    }

    public function getHelpBlockMessage($message)
    {
        $tid = TranslationLog::tid($message);
        if (isset($this->errors[$tid])) {
            return $this->errors[$tid];
        }

        if (isset($this->warnings[$tid])) {
            return $this->warnings[$tid];
        }

        return null;
    }

    public function getModuleIdSelection()
    {
        $moduleIds = BasePath::getModuleIds();
        array_walk($moduleIds, function (&$value, $key) {
            $value = $this->isCoreModule($key) ? 'HumHub - ' . $value : $value = 'Module - ' . $value;
            $value .= ' (' . TranslationCoverage::getModuleCoverage(BasePath::getBasePath($key), $this->language) . '%)';
        });
        asort($moduleIds);
        return $moduleIds;
    }

    private function isCoreModule($moduleId)
    {
        if ($moduleId === BasePath::CORE_MODULE_ID) {
            return true;
        }

        try {
            $module = Yii::$app->moduleManager->getModule($moduleId);

            if (strpos($module->getBasePath(), Yii::getAlias('@humhub')) !== false) {
                return true;
            }
        } catch (\Exception $ex) {
        }

        return false;
    }

    /**
     * @return array
     */
    public function getFilesSelection()
    {
        $result = [];
        foreach ($this->files as $file) {
            $baseName = $file->getBaseName();
            $coverage = TranslationCoverage::getFileCoverage($file, $this->language);
            if ($coverage !== false) {
                $result[$baseName] = $baseName . "($coverage%)";
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getLanguageSelection()
    {
        $result = [];
        foreach (Languages::getTranslatableUserLanguages() as $language) {
            $result[$language] = ($this->language === $language)
                ? $language . ' (' . TranslationCoverage::getLanguageCoverage($language) . '%)'
                : $language;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return '';
    }

    public function getMessageLanguage()
    {
        return $this->language;
    }

    public function getMessageModuleId()
    {
        return $this->moduleId;
    }

    public function getMessageBasename()
    {
        return $this->messageFile->getBaseName();
    }
}