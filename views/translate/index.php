<?php

use humhub\modules\translation\assets\MainAsset;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\widgets\TranslationFormWidget;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Button;

/* @var $model TranslationForm */

$bundle = MainAsset::register($this);

?>

<div id="translation-editor" class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= Icon::get('align-left') ?>  <?= Yii::t('TranslationModule.base', '<strong>Translation</strong> Editor') ?>
                    <?= Button::light(Yii::t('TranslationModule.base', 'Only show missing translations'))->id('toggle-empty-filter')
                        ->action('toggleEmptyTranslationFilter', null, '#translation-editor-translations')->loader(false)->sm()->icon('fa-toggle-off')->right() ?>
                </div>
                <?= TranslationFormWidget::widget([
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
