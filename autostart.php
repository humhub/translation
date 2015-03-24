<?php

Yii::app()->moduleManager->register(array(
    'id' => 'translation',
    'class' => 'application.modules.translation.TranslationModule',
    'import' => array(
        'application.modules.translation.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'AdminMenuWidget', 'event' => 'onInit', 'callback' => array('TranslationModule', 'onAdminMenuInit')),
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('TranslationModule', 'onTopMenuInit')),
        array('class' => 'CConsoleApplication', 'event' => 'onInit', 'callback' => array('TranslationModule', 'onConsoleApplicationInit')),
    ),
));
?>