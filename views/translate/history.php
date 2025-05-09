<?php
/* @var $this \humhub\components\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $messageFile \humhub\modules\translation\models\MessageFile  */
/* @var $message string */
/* @var $language string */

use humhub\modules\translation\assets\MainAsset;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\TranslationLog;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\GridView;
use yii\grid\DataColumn;
use yii\widgets\DetailView;

MainAsset::register($this);

?>

<div id="translation-history" class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading"><?= Icon::get('align-left') ?> <?= Yii::t('TranslationModule.base', '<strong>Translation</strong> History') ?></div>

                <div class="panel-body" data-ui-widget="translation.Form">
                    <div class="clearfix">
                        <?= Button::back(Url::toTranslation($messageFile, $language), Yii::t('TranslationModule.base', 'Back to editor'))->sm() ?>
                    </div>

                    <br>

                    <?= DetailView::widget([
                        'model' => $messageFile,
                        'attributes' => [
                            'moduleId',
                            [
                                'label' => Yii::t('TranslationModule.base','Language'),
                                'value' => $language
                            ],
                            [
                                'label' => Yii::t('TranslationModule.base','File'),
                                'value' => $messageFile->getBaseName()
                            ],
                            [
                                'label' => Yii::t('TranslationModule.base','Message'),
                                'value' => $message
                            ],

                            [
                                'label' => Yii::t('TranslationModule.base','Active translation'),
                                'value' => $messageFile->getTranslation($language, $message)
                            ],
                        ]
                    ])?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'emptyText' => Yii::t('TranslationModule.base', 'No translation log available.'),
                        'summary' => '',
                        'columns' => [
                            [
                                'class' => ImageColumn::class,
                                'userAttribute' => 'translator'
                            ],
                            [
                                'class' => DisplayNameColumn::class,
                                'userAttribute' => 'translator',
                                'options' => ['style' => 'width:10%;'],
                            ],
                            [
                                'attribute' => 'translation',
                                'options' => ['style' => 'width:65%;'],
                            ],
                            [
                                'class' => DataColumn::class,
                                'label' => Yii::t('TranslationModule.base', 'Date'),
                                //'options' => ['style' => 'width:75%;'],
                                'value' => function(TranslationLog $model) {
                                    return Yii::$app->formatter->asDatetime($model->content->created_at, 'short');
                                }
                            ],
                            [
                                'class' => DataColumn::class,
                                'format' => 'raw',
                                'label' => '',
                                //'options' => ['style' => 'width:75%;'],
                                'value' => function(TranslationLog $model) {
                                    return Button::light()->icon('comments-o')
                                        ->link(Url::toLogDetail($model))
                                        ->title(Yii::t('TranslationModule.base', 'Discussion'))
                                        ->cssClass('tt')
                                        ->sm();
                                }
                            ]
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
