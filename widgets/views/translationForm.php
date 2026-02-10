<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\translation\helpers\Url;
use humhub\modules\translation\models\forms\TranslationForm;
use humhub\modules\translation\models\TranslationLog;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use yii\web\NotFoundHttpException;

/* @var $this View */
/* @var $options array */
/* @var $model TranslationForm */

$errors = null;

if ($model->hasErrors()) {
    $errors = Html::errorSummary($model, [
        'header' => '<strong>' . Yii::t('TranslationModule.base', 'The translations for {settings} could not be loaded:', $model->getMessageSettingString()) . '</strong>',
    ]);

    // Fallback to default selection
    $model = new TranslationForm();
    $model->load([]);

    if (!$model->validate()) {
        throw new NotFoundHttpException();
    }
}

$hasParentLanguage = $model->getParentLanguage() !== null;
$canManage = $model->canManage();
?>

<?= Html::beginTag('div', $options) ?>

    <?php ActiveForm::begin(['id' => 'translation-editor-form', 'action' => Url::toSave($model),  'acknowledge' => true ]) ?>
        <div class="translation-editor-filter clearfix">
            <div class="row">
                <div class="mb-3 col-lg-2">
                    <label for=""><?= $model->getAttributeLabel('language') ?></label>
                    <?= Html::dropDownList('language', $model->language, $model->getLanguageSelection(), [
                        'class' => 'form-control',
                        'data-prevent-statechange' => 1,
                        'data-action-change' => 'selectOptions',
                    ]) ?>
                </div>
                <div class="mb-3 col-lg-4">
                    <label for=""><?= $model->getAttributeLabel('moduleId') ?></label>
                    <?= Html::dropDownList('moduleId', $model->moduleId, $model->getModuleIdSelection(), [
                        'class' => 'form-control',
                        'data-prevent-statechange' => 1,
                        'data-action-change' => 'selectOptions',
                    ]) ?>
                </div>
                <div class="mb-3 col-lg-6">
                    <label for=""><?= $model->getAttributeLabel('file') ?></label>
                    <?= Html::dropDownList('file', $model->file, $model->getFilesSelection(), [
                        'class' => 'form-control',
                        'data-prevent-statechange' => 1,
                        'data-action-change' => 'selectOptions',
                    ]) ?>
                </div>
            </div>
            <ul>
                <li><?= Yii::t('TranslationModule.base', 'If the value is empty, the message is considered as not translated.') ?></li>
                <li><?= Yii::t('TranslationModule.base', 'Messages that no longer need translation will have their translations enclosed between a pair of "@@" marks.') ?></li>
                <li>
                    <?= Yii::t('TranslationModule.base', 'Message string can be used with plural forms format. Check i18n section of the documentation for details.') ?>
                    <strong><a href="https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#plural" target="_blank">(Plural pattern)</a></strong>
                </li>
                <li> <?= Yii::t('TranslationModule.base', 'For more informations about translation syntax see') ?>
                    <strong><a href="http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html" target="_blank">Yii Framework Guide I18n</a></strong>.
                </li>
            </ul>


        </div>

        <div class="panel-body">

            <?php if (!empty($errors)) : ?>

                <div class="alert alert-danger">
                    <?= $errors ?>

                    <?= Yii::t('TranslationModule.base', 'If you are responsible for this module, try running the following command:')?>
                    &nbsp;<code>php yii message/extract-module myModuleId</code>
                    <br>
                    <?= Yii::t('TranslationModule.base', 'Otherwise, please report this to the module owner or translation admin.')?>
                </div>

            <?php else: ?>

                <div class="clearfix">
                    <p class="float-start">
                        <?= Html::textInput('search', null, [
                            'class' => 'form-control form-search',
                            'placeholder' => Yii::t('TranslationModule.base', 'Search'),
                            'data-action-keydown' => 'search']) ?>
                    </p>
                </div>

                <p class="clearfix" style="margin-bottom:0">
                    <?= $canManage ? Button::save()->submit()->right() : '' ?>
                </p>

                <hr class="mt-0">

                <div id="words">
                    <div>
                        <div class="elem"><?= Yii::t('TranslationModule.base', 'Original (en-US)') ?></div>
                        <div class="elem"><?= Yii::t('TranslationModule.base', 'Translated') ?> (<?= Html::encode($model->language) ?>)</div>
                    </div>

                    <?php foreach ($model->messages as $original => $translated) : ?>
                        <div class="item">
                            <div class="elem">
                                <div class="pre"><?= Html::encode($original) ?></div>
                                <div>
                                    <?= $canManage
                                        ? Button::light('<span>' . Yii::t('TranslationModule.base', 'Adopt original language') . '</span>')
                                        ->icon('arrow-right')
                                        ->action('copyOriginal')
                                        ->tooltip(Yii::t('TranslationModule.base', 'Adopt original language'))
                                        ->loader(false)
                                        : '' ?>
                                </div>
                            </div>
                            <div class="elem <?= $model->getTranslationFieldClass($original) ?>">
                                <div>
                                    <?php if ($canManage) : ?>
                                        <?= Html::textArea(TranslationLog::tid($original), $translated, [
                                            'class' => 'form-control translation ' . (empty($translated) ? 'empty' : 'translated'),
                                            'placeholder' => $model->parentMessages[$original] ?? '',
                                        ]) ?>
                                    <?php else : ?>
                                        <div class="pre"><?= Html::encode($translated) ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($model->getHelpBlockMessage($original))) : ?>
                                        <p class="form-text"><?= Html::encode($model->getHelpBlockMessage($original)) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                <?= Button::light('<span>' . Yii::t('TranslationModule.base', 'View history') . '</span>')
                                    ->link(Url::toHistory($model, $original))
                                    ->icon('history')
                                    ->tooltip(Yii::t('TranslationModule.base', 'View translation history'))
                                    ->loader(false) ?>

                                <?= $hasParentLanguage && $canManage
                                    ? Button::success('<span>' . Yii::t('TranslationModule.base', 'Confirm translation') . '</span>')
                                    ->icon('check')
                                    ->action('copyParent')
                                    ->tooltip(Yii::t('TranslationModule.base', 'Confirm translation'))
                                    ->cssClass($translated === '' ? '' : 'translation-confirm-approved')
                                    ->loader(false)
                                    : '' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
                <hr>

                <p class="clearfix">
                    <?= $canManage ? Button::save()->submit()->right() : '' ?>
                </p>
            <?php endif; ?>
        </div>
    <?php ActiveForm::end() ?>
<?= Html::endTag('div') ?>
