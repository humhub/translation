<?php

namespace humhub\modules\translation;

use Yii;
use humhub\components\Module as HumHubModule;
use humhub\modules\translation\models\BasePath;

class Module extends HumHubModule
{
    /**
     * @var string Google API key (optional, if empty, no automatic translation)
     * For automatic translation:
     * - Activate the API Cloud Translation: https://console.developers.google.com/apis/library
     * - Get your Google API key: https://console.developers.google.com/apis/api/translate.googleapis.com/credentials
     */
    public $googleApiKey;

    /**
     * @var string Google API URL V2
     */
    public $googleApiUrl = 'https://www.googleapis.com/language/translate/v2';


    /**
     * Returns all Messages
     *
     * @param type $lang
     * @param string $section
     * @return array
     */
    public function getTranslationMessages($file)
    {
        return require($file);
    }

}
