<?php


namespace humhub\modules\translation\widgets;

use humhub\modules\content\widgets\EditLink;
use humhub\modules\content\widgets\PermaLink;
use humhub\modules\content\widgets\WallEntry as BaseWallEntry;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\TranslationLog;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\Link;
use Yii;

class WallEntry extends BaseWallEntry
{
    public function run()
    {
        return $this->render('wallEntry', ['translationLog' => $this->contentObject, 'justEdited' => $this->justEdited]);
    }

    public function getContextMenu()
    {
        /* @var $model TranslationLog */
        $model = $this->contentObject;
        $result = [];
        $this->addControl($result, [PermaLink::class, ['content' => $model], ['sortOrder' => 200]]);
        $this->addControl($result, new MenuLink([
            'link' => Link::to(Yii::t('TranslationModule.base', 'View History'), Url::toHistory($model, $model->message))->icon('history'),
            'sortOrder' => 50,
        ]));
        $this->addControl($result, [EditLink::class, [
            'model' => $this->contentObject, 'mode' => static::EDIT_MODE_NEW_WINDOW, 'url' => Url::toEditTranslation($this->contentObject)
        ], ['sortOrder' => 100]]);
        return $result;
    }
}