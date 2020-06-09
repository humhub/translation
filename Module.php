<?php

namespace humhub\modules\translation;

use Yii;
use humhub\components\Module as HumHubModule;
use humhub\modules\translation\models\BasePath;

class Module extends HumHubModule
{
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
