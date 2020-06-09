<?php

namespace humhub\modules\translation\commands;

use humhub\modules\translation\models\Languages;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

class TranslationController extends Controller
{

    /**
     * Automatically translates message duplicates.
     *
     * @see DuplicateTranslator
     * @throws Exception
     */
    public function actionHandleDuplicate()
    {
        foreach (Languages::getAllTranslatableLanguages() as $language) {
           DuplicateTranslator::translateDuplicatesForLanguage($language);
        }
    }

    public function actionRenameCategory($moduleId, $oldCategory, $newCategory)
    {
        RenameTranslationCategory::rename($moduleId, $oldCategory, $newCategory);
    }

    public function actionBuildArchive()
    {
        BuildArchive::run();
    }

}
