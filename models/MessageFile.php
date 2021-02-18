<?php


namespace humhub\modules\translation\models;


use humhub\modules\translation\models\validators\MessageFileNameValidator;
use Yii;
use yii\base\Exception;

class MessageFile extends TranslationPath
{

    /**
     * @var BasePath
     */
    public $basePath;

    /**
     * @var string
     */
    public $file;

    public function init()
    {
        parent::init();

        if(!($this->basePath instanceof BasePath)) {
            $this->basePath = BasePath::getBasePath($this->moduleId);
        }

        $this->moduleId = $this->basePath->moduleId;
    }

    public function rules()
    {
        return array_merge([
            [['file', 'basePath'], 'required'],
            [['file'], 'string'],
            ['file', MessageFileNameValidator::class],
            ['basePath', function() {
                if(!$this->basePath->validate()) {
                    $this->addError('basePath', 'Invalid basepath');
                }
            }],
        ], parent::rules());
    }

    public function attributeLabels()
    {
        return [
            'moduleId' => Yii::t('TranslationModule.base', 'Module'),
            'file' => Yii::t('TranslationModule.base', 'File'),
        ];
    }

    public function getFileName()
    {
        return $this->getBaseName().'.php';
    }

    public function getBaseName()
    {
        return basename($this->file, '.php');
    }

    /**
     * Returns the message path for a given language
     *
     * @param string|null $language
     * @param bool $validate
     * @return bool|string
     * @throws Exception
     */
    public function getPath($language = null, $validate = true)
    {
        if(!$language) {
            return null;
        }

        $filePath = $this->basePath->getPath($language) . DIRECTORY_SEPARATOR . $this->getFileName();

        return !$validate || is_file($filePath) ? $filePath : null;
    }

    public function getTranslation($language, $message)
    {
        return $this->getMessages($language)[$message] ?? null;
    }

    public function getMessages($language)
    {
        if(!$this->validate() || !$this->validateLanguagePath($language)) {
            return [];
        }

        return require($this->getPath($language));
    }

    public function validateLanguagePath($language)
    {
        $filePath = realpath($this->getPath($language));

        if(!$filePath || !is_file($filePath)) {
            return false;
        }

        $languageDir = dirname($filePath);
        $messages = dirname($languageDir);
        $moduleId = dirname($messages);

        $expectedModuleId = $this->isCoreModulePath() ? 'humhub' : basename($this->module->getBasePath());

        if(!($this->validateParent($languageDir, $language) || $this->validateParent($languageDir, static::toLegacyLanguageCode($language)))
            || !$this->validateParent($messages, 'messages')
            || !$this->validateParent($moduleId, $expectedModuleId)) {
            return false;
        }

        return is_file($this->getPath($language));
    }

    private function validateParent($path, $expected)
    {
        return basename($path) === $expected;
    }

    public function updateTranslations($language, $messages, $create = false)
    {
        if(!$create && (!$this->validate() || !$this->validateLanguagePath($language))) {
            return false;
        }

        ksort($messages);
        $array = str_replace("\r", '', var_export($messages, true));
        $content = <<<EOD
<?php
return $array;

EOD;

        file_put_contents($this->getPath($language, false), $content);
        return true;
    }
}