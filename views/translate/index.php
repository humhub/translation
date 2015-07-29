<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo Yii::t('TranslationModule.views_translate_index', 'Translation Editor'); ?></div>
                <div class="panel-body">

                    <?php echo Html::beginForm(Url::to(['/translation/translate']), 'GET'); ?>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="">Module</label>
                            <?php echo Html::dropDownList('moduleId', $moduleId, $moduleIds, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="">Language</label>
                            <?php echo Html::dropDownList('language', $language, $languages, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">File</label>
                            <?php echo Html::dropDownList('file', $file, $files, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
                        </div>

                    </div>
                    <hr>

                    <span style='color:red'>Save before change!</span>
                    <?php echo Html::endForm(); ?>



                    <?php $i = 0; ?>

                    <?php echo Html::beginForm(Url::to(['/translation/translate/save', 'language' => $language, 'file' => $file, 'moduleId' => $moduleId]), 'POST'); ?>
                    <?php //echo Html::hiddenInput('language', $language); ?>
                    <?php //echo Html::hiddenInput('file', $file); ?>
                    <?php //echo Html::hiddenInput('moduleId', $moduleId); ?>
                    <p>
                        If the value is empty, the message is considered as not translated.
                        Messages that no longer need translation will have their translations enclosed between a pair of '@@' marks.
                        Message string can be used with plural forms format. Check i18n section of the documentation for details.
                        <br/>
                        <br/>
                        For more informations about translation syntax see <a
                            href="http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html">Yii Framework Guide I18n</a>.

                    </p>

                    <p><?php echo Html::submitButton(Yii::t('TranslationModule.views_translate_index', 'Save'), array('class' => 'btn btn-primary')); ?></p>

                    <hr>
                    <table border="0" width="100%">
                        <tr>
                            <th style="width:50%;max-width:50%;padding-left:10px;">Original (en)</th>
                            <th style="width:50%;max-width:50%;padding-left:10px;">Translated (<?php echo $language; ?>)</th>
                        </tr>

                        <?php foreach ($messages as $orginal => $translated) : ?>
                            <tr style=''>
                                <?php $i++; ?>
                                <td style="width:50%;max-width:50%; padding:10px;">
                                    <pre style=""><?php print Html::encode($orginal); ?></pre>
                                </td>
                                <td style="width:50%;max-width:50%; padding:10px;"><?php echo Html::textArea('tid_' . md5($orginal), $translated, array('class' => 'form-control')); ?></td>
                            </tr>
                        <?php endforeach; ?>

                    </table>
                    <hr>
                    <p><?php echo Html::submitButton(Yii::t('TranslationModule.views_translate_index', 'Save'), array('class' => 'btn btn-primary')); ?></p>

                    <?php echo Html::endForm(); ?>

                </div>

            </div>

        </div>

    </div>
</div>    
