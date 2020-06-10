<?php

namespace humhub\modules\translation\models;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\Module;
use humhub\modules\translation\widgets\WallEntry;
use Yii;
use yii\helpers\HtmlPurifier;

/**
 * Class Translation used for logging translations.
 *
 * @property integer $id
 * @property string $language
 * @property string $module_id
 * @property string $file
 * @property string $message
 * @property string $translation_old
 * @property string $translation
 */
class TranslationLog extends ContentActiveRecord implements TranslationFileIF
{
    /**
     * @var BasePath
     */
    private $basePath;

    /**
     * @var MessageFile
     */
    private $messageFile;

    /**
     * @inheritDoc
     */
    public $streamChannel = 'translation';

    /**
     * @var bool
     */
    public $wasPurified = false;

    /**
     * @inheritDoc
     */
    public $managePermission = ManageTranslations::class;

    /**
     * @inheritDoc
     */
    public $wallEntryClass = WallEntry::class;

    /**
     * @param MessageFile $messageFile
     * @param $language
     * @param $message
     * @return \humhub\modules\content\components\ActiveQueryContent
     */
    public static function findHistory(MessageFile $messageFile, $language, $message)
    {
        return static::find()->where([
            'language' => $language,
            'module_id' => $messageFile->moduleId,
            'file' => $messageFile->getBaseName(),
            'message' => $message
        ])->orderBy('id desc');
    }

    public function getModuleId()
    {
        return 'translation';
    }

    public function getTranslator()
    {
        return $this->content->createdBy;
    }

    public function getIcon()
    {
        return 'align-left';
    }

    public function getContentName()
    {
        return Yii::t('TranslationModule.base', 'Translation');
    }

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'translation_log';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['translation', 'validateTranslation'],
            [['translation'], 'trim'],
            [['language','module_id', 'file', 'message', 'translation'], 'string'],
            [['language','module_id', 'file', 'message', 'translation'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'module_id' => Yii::t('TranslationModule.base', 'Module'),
            'file' => Yii::t('TranslationModule.base', 'File'),
            'message' => Yii::t('TranslationModule.base', 'Message'),
            'translation_old' => Yii::t('TranslationModule.base', 'Old Translation'),
            'translation' => Yii::t('TranslationModule.base', 'Translation')
        ];
    }

    public function validateTranslation()
    {
        if($this->translation === $this->translation_old) {
            $this->addError('translation_old', Yii::t('TranslationModule.base', 'Translation did not change.'));
        }

        if(empty($this->translation)) {
            $this->addError('translation', Yii::t('TranslationModule.base', 'Your translation seems to be empty and therefore could not be saved.'));
            return;
        }

        $this->purifyTranslation();
        $this->validateTranslationParams();
    }

    public function getBasePath()
    {
        if(!$this->basePath) {
            $this->basePath = BasePath::getBasePath($this->module_id);
        }

        return $this->basePath;
    }

    /**
     * @return MessageFile
     */
    public function getMessageFile()
    {
        if(!$this->messageFile) {
            $this->messageFile = $this->getBasePath()->getMessageFile($this->file);
        }

        return $this->messageFile;
    }

    private function validateTranslationParams()
    {
        $params = [];

        preg_match_all('/{([a-zA-Z]+),([a-zA-Z]+),/m', $this->message, $messageMatch, PREG_SET_ORDER);
        preg_match_all('/{([a-zA-Z]+),([a-zA-Z]+),/m', $this->translation, $translationMatch, PREG_SET_ORDER);


        if(!$this->compareParameter($messageMatch, $translationMatch)) {
            return;
        }

        // Assamble parameter from e.g. {n,plural,=1{# space} other{# spaces}}
        foreach ($translationMatch as $match) {
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

        preg_match_all('/{([a-zA-Z]+)}/m', $this->translation, $translationMatch, PREG_SET_ORDER);
        preg_match_all('/{([a-zA-Z]+)}/m', $this->message, $messageMatch, PREG_SET_ORDER);

        if(!$this->compareParameter($messageMatch, $translationMatch)) {
            return;
        }

        foreach ($translationMatch as $match) {
            $params[$match[1]] = 'Test Value';
        }

        // Test translation run
        $formatter = Yii::$app->getI18n()->getMessageFormatter();
        $formatter->format($this->translation, $params, $this->language);
        if ($formatter->getErrorMessage()) {
            $this->addError('translation', Yii::t('TranslationModule.base', 'Invalid translation pattern detected, please see {link}', [
                'error' => $formatter->getErrorMessage(), 'link' => 'https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#message-formatting'
            ]));
        }
    }

    private function compareParameter($messageMatches, $translationMatches, $index = 1)
    {
        array_walk($messageMatches, function(&$value) use ($index) {
            $value = $value[$index] ?? null;
        });

        array_walk($translationMatches, function(&$value) use ($index) {
            $value = $value[$index] ?? null;
        });

        $diff = array_diff($messageMatches, $translationMatches);

        if(!empty($diff)) {
            $this->addError('translation',
                Yii::t('TranslationModule.base', 'The translation is missing a parameter "{match}"', ['match' => array_values($diff)[0]]));
            return false;
        }

        $diff = array_diff($translationMatches, $messageMatches);

        if(!empty($diff)) {
            $this->addError('translation',
                Yii::t('TranslationModule.base', 'TThe translation contains an invalid parameter "{match}"', ['match' => array_values($diff)[0]]));
            return false;
        }

        return true;
    }

    private function purifyTranslation()
    {
        $translationPurified = HtmlPurifier::process($this->translation);

        if ($this->translation !== $translationPurified) {
            $this->wasPurified = true;
        }

        $this->translation = $translationPurified;
    }

    public function getTID()
    {
        return static::tid($this->message);
    }

    public function load($data, $formName = null)
    {
        if(isset($data[$this->getTID()])) {
            $this->translation = trim($data[$this->getTID()]);
            return true;
        }

        return false;
    }

    public static function tid($message)
    {
        return 'tid_' . md5($message);
    }

    public function getUrl()
    {
        return Url::toLogDetail($this);
    }

    public function getMessageLanguage()
    {
        return $this->language;
    }

    public function getMessageModuleId()
    {
        return $this->module_id;
    }

    public function getMessageBasename()
    {
        return $this->getMessageFile()->getBaseName();
    }

    public function getMessage()
    {
        return $this->message;
    }
}