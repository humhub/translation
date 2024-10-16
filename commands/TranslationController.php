<?php

namespace humhub\modules\translation\commands;

use humhub\modules\translation\models\Languages;
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

    /**
     * Rename category, use * for old category param if need to rename all old categories to new one, or separate old categories with comma
     *
     * @param $moduleId
     * @param $oldCategory
     * @param $newCategory
     * @return void
     * @throws Exception
     */
    public function actionRenameCategory($moduleId, $oldCategory, $newCategory)
    {
        RenameTranslationCategory::rename($moduleId, $oldCategory, $newCategory);
    }

    public function actionBuildArchive()
    {
        BuildArchive::run();
    }

}
