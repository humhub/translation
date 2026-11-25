<?php

use humhub\helpers\Html;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\translation\assets\MainAsset;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\widgets\TranslationLogStreamViewer;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\Icon;

/* @var $this \humhub\components\View */

MainAsset::register($this);

?>

<div class="card content-card">
    <div class="card-header">
        <div class="card-title"><?= Icon::get('history') ?> <?= Yii::t('TranslationModule.base', '<strong>Translation</strong> history') ?></div>
        <div class="card-header-actions">
            <?= Button::primary(Yii::t('TranslationModule.base', 'Edit translations'))
                ->link(Url::toEditSpaceTranslation(ContentContainerHelper::getCurrent()))->sm()->icon('pencil') ?>
        </div>
    </div>
</div>

<?= TranslationLogStreamViewer::widget([
    'contentContainer' => ContentContainerHelper::getCurrent(),
    'messageStreamEmpty' => Yii::t('TranslationModule.base', 'No translation history available.')
]) ?>
