<?php

use humhub\widgets\TopMenu;
use humhub\components\console\Application;

return [
    'id' => 'translation',
    'class' => 'humhub\modules\translation\Module',
    'namespace' => 'humhub\modules\translation',
    'events' => array(
        array('class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => array('humhub\modules\translation\Module', 'onTopMenuInit')),
        array('class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => array('humhub\modules\translation\Module', 'onConsoleApplicationInit')),
    ),
];
?>