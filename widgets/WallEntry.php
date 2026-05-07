<?php

namespace humhub\modules\translation\widgets;

use humhub\libs\Helpers;
use humhub\modules\content\widgets\EditLink;
use humhub\modules\content\widgets\PermaLink;
use humhub\modules\content\widgets\stream\WallStreamModuleEntryWidget;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\TranslationLog;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\bootstrap\Link;
use Yii;

class WallEntry extends WallStreamModuleEntryWidget
{
    /**
     * @var TranslationLog
     */
    public $model;

    /**
     * @inheritdoc
     */
    protected function getTitle()
    {
        return Helpers::trimText($this->model->message, 80);
    }

    /**
     * @inheritdoc
     */
    protected function renderContent()
    {
        return $this->render('wallEntry', [
            'translationLog' => $this->model,
            'justEdited' => $this->renderOptions->justEdited,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getControlsMenuEntries()
    {
        return [
            new MenuLink([
                'link' => Link::to(
                    Yii::t('TranslationModule.base', 'View History'),
                    Url::toHistory($this->model, $this->model->message),
                )->icon('history'),
                'sortOrder' => 50,
            ]),
            [
                EditLink::class,
                [
                    'model' => $this->model,
                    'mode' => static::EDIT_MODE_NEW_WINDOW,
                    'url' => Url::toEditTranslation($this->model),
                ],
                ['sortOrder' => 100],
            ],
            [
                PermaLink::class,
                ['content' => $this->model],
                ['sortOrder' => 200],
            ],
        ];
    }
}
