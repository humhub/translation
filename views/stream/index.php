<?php

use humhub\libs\Html;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\translation\assets\MainAsset;
use humhub\modules\translation\widgets\TranslationLogStreamViewer;

/* @var $this \humhub\components\View */

MainAsset::register($this);

?>

<?= TranslationLogStreamViewer::widget([
    'contentContainer' => ContentContainerHelper::getCurrent()
]) ?>
