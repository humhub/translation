<?php
/* @var $this View */
/* @var $translationLog TranslationLog */

use humhub\modules\ui\icon\widgets\Icon;
use humhub\libs\Html;
use humhub\modules\translation\models\TranslationLog;
use yii\widgets\DetailView;

?>

<div class="clearfix translation-wallentry-content">
    <?= DetailView::widget([
        'model' => $translationLog,
        'attributes' => [
            'moduleId',
            [
                'attribute' => 'message',
                'value' => '"'.$translationLog->message.'"',
                'contentOptions' => [
                    'class' => 'translation_message'
                ]
            ],
            [
                'attribute' => 'translation_old',
                'label' => Yii::t('TranslationModule.base', 'Old'),
                'format' => 'raw',
                'value' => Icon::get('minus-circle').' "'.Html::encode($translationLog->translation_old).'"',
                'contentOptions' => [
                    'class' => 'translation_old'
                ]
            ],
            [
                'attribute' => 'translation',
                'label' => Yii::t('TranslationModule.base', 'New'),
                'format' => 'raw',
                'value' => Icon::get('plus-circle').' "'.Html::encode($translationLog->translation).'"',
                'contentOptions' => [
                    'class' => 'translation_new'
                ]
            ],
        ]
    ])?>

</div>
