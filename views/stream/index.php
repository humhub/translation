<?php

use humhub\helpers\Html;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\translation\assets\MainAsset;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\widgets\TranslationLogStreamViewer;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Button;

/* @var $this \humhub\components\View */

MainAsset::register($this);

?>

<div class="panel">
    <div class="panel-heading">
        <?= Icon::get('history') ?> <?= Yii::t('TranslationModule.base', '<strong>Translation</strong> history') ?>
        <?= Button::primary(Yii::t('TranslationModule.base', 'Edit translations'))
            ->link(Url::toEditSpaceTranslation(ContentContainerHelper::getCurrent()))->right()->sm()->icon('pencil') ?>
    </div>
</div>

<?= TranslationLogStreamViewer::widget([
    'contentContainer' => ContentContainerHelper::getCurrent(),
    'messageStreamEmpty' => Yii::t('TranslationModule.base', 'No translation history available.')
]) ?>
