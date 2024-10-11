<?php

namespace humhub\modules\translation\widgets;

use humhub\modules\stream\widgets\StreamViewer;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\stream\filters\ModuleIdFilter;

class TranslationLogStreamViewer extends StreamViewer
{
    public $streamFilterNavigation = StreamFilterNavigation::class;
    public $filters = [
        StreamFilterNavigation::FILTER_ORIGINATORS,
        ModuleIdFilter::ID,
    ];
    public $streamAction = Url::ROUTE_STREAM;
}
