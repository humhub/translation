<?php

use humhub\modules\translation\Module;
use humhub\widgets\TopMenu;
use humhub\components\console\Application;
use humhub\modules\translation\Events;
use humhub\modules\space\widgets\Menu;

return [
    'id' => 'translation',
    'class' => Module::class,
    'namespace' => 'humhub\modules\translation',
    'events' => [
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => Events::onTopMenuInit(...)],
        ['class' => Menu::class, 'event' => Menu::EVENT_INIT, 'callback' => Events::onSpaceMenuInit(...)],
        ['class' => Application::class, 'event' => Application::EVENT_ON_INIT, 'callback' => Events::onConsoleApplicationInit(...)],
    ],
];
