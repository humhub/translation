<?php

use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\widgets\TranslationFormWidget;

use humhub\modules\translation\assets\MainAsset;
use humhub\modules\ui\icon\widgets\Icon;

/* @var $model TranslationForm */

$bundle = MainAsset::register($this);

?>

<div id="translation-editor" class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading"><?= Icon::get('align-left') ?>  <?= Yii::t('TranslationModule.views_translate_index', '<strong>Translation</strong> Editor') ?></div>
                   <?= TranslationFormWidget::widget([
                       'model' => $model,
                   ])?>
            </div>
        </div>
    </div>
</div>
