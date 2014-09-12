<?php

class TranslationModule extends HWebModule
{

    /**
     * On AdminNavigationWidget init, this callback will be called
     * to add some extra navigation items.
     * 
     * (The event is catched in example/autostart.php)
     * 
     * @param type $event
     */
    public static function onAdminMenuInit($event)
    {
        $event->sender->addItem(array(
            'label' => Yii::t('TranslationModule.base', 'Translation Manager'),
            'url' => Yii::app()->createUrl('//translation/translate'),
            'icon' => '<i class="fa fa-align-left"></i>',
            'group' => 'manage',
            'sortOrder' => 1000,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'translation' && Yii::app()->controller->id == 'translate'),
            'newItemCount' => 0
        ));
    }

    public static function onTopMenuInit($event)
    {
        $event->sender->addItem(array(
            'label' => Yii::t('TranslationModule.base', 'Translations'),
            'url' => Yii::app()->createUrl('//translation/translate', array('uguid' => Yii::app()->user->guid)),
            'icon' => '<i class="fa fa-align-left"></i>',
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'translation'),
            'sortOrder' => 700,
        ));
    }

}
