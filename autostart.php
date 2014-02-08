<?php

Yii::app()->moduleManager->register(array(
    'id' => 'translation',
    'class' => 'application.modules.translation.TranslationModule',
    'title' => Yii::t('TranslationModule.base', 'Translation manager'),
    'description' => Yii::t('TranslationModule.base', 'Simple translation manager.'),
    'import' => array(
        'application.modules.translation.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'AdminMenuWidget', 'event' => 'onInit', 'callback' => array('TranslationModule', 'onAdminMenuInit')),
    ),
));
?>