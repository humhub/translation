<?php

namespace humhub\modules\translation\stream;

use humhub\modules\stream\models\ContentContainerStreamQuery;
use humhub\modules\stream\models\filters\OriginatorStreamFilter;
use humhub\modules\translation\stream\filters\ModuleIdFilter;

class StreamQuery extends ContentContainerStreamQuery
{


    public $filterHandlers = [
        ModuleIdFilter::class,
        OriginatorStreamFilter::class,
    ];

    public $pinnedContentSupport = false;
    protected $preventSuppression = true;

    public $channel = 'translation';

    protected function setupCriteria()
    {
        parent::setupCriteria();
        $this->_query->innerJoin('translation_log', 'content.object_id = translation_log.id');
    }
}