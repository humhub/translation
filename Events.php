<?php

namespace humhub\modules\translation;

use humhub\helpers\ControllerHelper;
use humhub\modules\space\widgets\Menu as SpaceMenu;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\Languages;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\translation\commands\TranslationController;
use humhub\widgets\TopMenu;
use Yii;

class Events
{
    public static function onTopMenuInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        /* @var TopMenu $menu */
        $menu = $event->sender;

        $menu->addEntry(new MenuLink([
            'id' => 'translation-main',
            'icon' => 'align-left',
            'label' => Yii::t('TranslationModule.base', 'Translations'),
            'url' => ['/translation/translate'],
            'sortOrder' => 700,
            'isActive' => ControllerHelper::isActivePath('translation', 'translate'),
        ]));
    }

    public static function onSpaceMenuInit($event)
    {
        $space = $event->sender->space;

        if (!Languages::getLanguageBySpaceName($space)) {
            return;
        }

        /* @var SpaceMenu $menu */
        $menu = $event->sender;

        $menu->addEntry(new MenuLink([
            'id' => 'translation-space',
            'icon' => 'align-left',
            'label' => Yii::t('TranslationModule.base', 'Translations'),
            'url' => Url::toStream($space),
            'sortOrder' => 700,
            'isActive' => ControllerHelper::isActivePath('translation', 'stream'),
        ]));
    }

    public static function onConsoleApplicationInit($event)
    {
        $event->sender->controllerMap['translation'] = TranslationController::class;
    }
}
