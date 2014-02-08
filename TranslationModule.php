<?php

class TranslationModule extends CWebModule {

    /**
     * On AdminNavigationWidget init, this callback will be called
     * to add some extra navigation items.
     * 
     * (The event is catched in example/autostart.php)
     * 
     * @param type $event
     */
    public function onAdminMenuInit($event) {
        $event->sender->addItem(array(
            'label' => Yii::t('TranslationModule.base', 'Translation'),
            'url' => Yii::app()->createUrl('//translation/translate'),
            'icon' => '<i class="icon-user"></i>',
            'group' => 'manage',
            'sortOrder' => 1000,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'translation' && Yii::app()->controller->id == 'translate'),
            'newItemCount' => 0
        ));
        
    }
    
}