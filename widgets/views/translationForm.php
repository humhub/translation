<?php

use humhub\components\View;
use humhub\libs\Html;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\TranslationLog;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $this View */
/* @var $options array */
/* @var $model TranslationForm */

?>

<?= Html::beginTag('div', $options) ?>

    <?php ActiveForm::begin(['id' => 'translation-editor-form', 'action' => Url::toSave($model),  'acknowledge' => true ]) ?>
        <div class="translation-editor-filter clearfix">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for=""><?= $model->getAttributeLabel('moduleId') ?></label>
                    <?= Html::dropDownList('moduleId', $model->moduleId, $model->getModuleIdSelection(),
                        ['class' => 'form-control', 'data-ui-select2' => '1', 'data-prevent-statechange' => 1, 'data-action-change' => 'selectOptions']) ?>
                </div>
                <div class="form-group col-md-2">
                    <label for=""><?= $model->getAttributeLabel('language') ?></label>
                    <?= Html::dropDownList('language', $model->language, $model->getLanguageSelection(), ['class' => 'form-control', 'data-ui-select2' => '1',
                        'data-prevent-statechange' => 1,
                        'data-action-change' => 'selectOptions']) ?>
                </div>
                <div class="form-group col-md-6">
                    <label for=""><?= $model->getAttributeLabel('file') ?></label>
                    <?= Html::dropDownList('file', $model->file, $model->getFilesSelection(), ['class' => 'form-control', 'data-ui-select2' => '1',
                        'data-prevent-statechange' => 1,
                        'data-action-change' => 'selectOptions']) ?>
                </div>
            </div>
            <ul>
                <li><?= Yii::t('TranslationModule.views_translate_index', 'If the value is empty, the message is considered as not translated.') ?></li>
                <li><?= Yii::t('TranslationModule.views_translate_index', 'Messages that no longer need translation will have their translations enclosed between a pair of "@@" marks.') ?></li>
                <li>
                    <?= Yii::t('TranslationModule.views_translate_index', 'Message string can be used with plural forms format. Check i18n section of the documentation for details.') ?>
                    <strong><a href="https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#plural" target="_blank">(Plural pattern)</a></strong>
                </li>
                <li> <?= Yii::t('TranslationModule.views_translate_index', 'For more informations about translation syntax see') ?>
                    <strong><a href="http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html" target="_blank">Yii Framework Guide I18n</a></strong>.
                </li>
            </ul>


        </div>

        <div class="panel-body">

            <p style="float:left">
                <?= Html::textInput('search', null, [
                    'class' => 'form-control form-search',
                    'placeholder' => Yii::t('TranslationModule.views_translate_index', 'Search'),
                    'data-action-keydown' => 'search']) ?>
            </p>



            <p class="clearfix" style="margin-bottom:0">
                <?= Button::save()->submit()->right()?>
            </p>

            <hr style="margin-top:0">

            <div id="words">
                <div>
                    <div class="elem"><?= Yii::t('TranslationModule.views_translate_index', 'Original (en-US)') ?></div>
                    <div class="elem"><?= Yii::t('TranslationModule.views_translate_index', 'Translated') ?> (<?= Html::encode($model->language) ?>)</div>
                </div>

                <?php foreach ($model->messages as $original => $translated) : ?>
                    <div class="row ">
                        <div class="elem">
                            <div class="pre"><?= Html::encode($original) ?></div>
                        </div>
                        <div class="form-group elem <?= $model->getTranslationFieldClass($original)?>" style="position:relative">
                            <?= Html::textArea(TranslationLog::tid($original), $translated, ['class' => 'form-control translation '.(empty($translated) ? 'empty' : 'translated')]) ?>

                            <?php if(!empty($model->getHelpBlockMessage($original))) : ?>
                                <p class="help-block"><?= Html::encode($model->getHelpBlockMessage($original)) ?></p>
                            <?php endif; ?>

                            <?= Button::asLink(null, Url::toHistory($model, $original))
                                ->icon('history')
                                ->title(Yii::t('TranslationModule.base', 'View history'))
                                ->cssClass('translation-history-button tt') ?>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>
            <hr>

            <p class="clearfix"><?= Button::save()->submit()->right() ?></p>
        </div>
    <?php ActiveForm::end() ?>
<?= Html::endTag('div') ?>
