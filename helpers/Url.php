<?php

namespace humhub\modules\translation\helpers;

use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\Languages;
use humhub\modules\translation\models\MessageFile;
use humhub\modules\translation\models\TranslationFileIF;
use humhub\modules\translation\models\TranslationLog;

class Url extends \yii\helpers\Url
{
    const ROUTE_STREAM = '/translation/stream/stream';

    public static function toTranslation(MessageFile $messageFile, $language)
    {
        return static::to(['/translation/translate/index',
            'language' => $language,
            'moduleId' => $messageFile->moduleId,
            'file' => $messageFile->getBaseName()]);
    }

    public static function toEditTranslation(TranslationFileIF $model)
    {
        return static::to(['/translation/translate/index',
            'language' => $model->getMessageLanguage(),
            'moduleId' => $model->getMessageModuleId(),
            'file' => $model->getMessageBasename()]);
    }

    public static function toEditSpaceTranslation($space)
    {
        return static::to(['/translation/translate/index',
            'language' => Languages::getLanguageBySpaceName($space)]);
    }

    public static function toHistory(TranslationFileIF $model, $message)
    {
        return static::to(['/translation/translate/history',
            'language' => $model->getMessageLanguage(),
            'moduleId' => $model->getMessageModuleId(),
            'file' => $model->getMessageBasename(),
            'message' => $message
        ]);
    }

    public static function toLogDetail(TranslationLog $model)
    {
        return $model->content->container->createUrl('/translation/stream', [
           'contentId' => $model->content->id
        ]);
    }

    public static function toReloadForm()
    {
        return static::to(['/translation/translate/index']);
    }

    public static function toSave(TranslationFileIF $model)
    {
        return static::to(['/translation/translate/save',
            'language' => $model->getMessageLanguage(),
            'moduleId' => $model->getMessageModuleId(),
            'file' => $model->getMessageBasename()]);
    }

    public static function toStream($space)
    {
        return $space->createUrl('/translation/stream/index');
    }
}