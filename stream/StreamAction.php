<?php

namespace humhub\modules\translation\stream;

use humhub\modules\stream\actions\ContentContainerStream;

class StreamAction extends ContentContainerStream
{
    public $streamQueryClass = StreamQuery::class;
}