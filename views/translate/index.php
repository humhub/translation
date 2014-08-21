<div class="container">
    <!-- Example row of columns -->
    <div class="row">
        <div class="col-md-12">


            <div class="panel panel-default">
                <div class="panel-heading"><?php echo Yii::t('TranslationModule.views_translate_index', 'Translation Editor'); ?></div>
                <div class="panel-body">

                    <?php echo CHtml::beginForm($this->createUrl('//translation/translate'), 'GET'); ?>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="">Module</label>
                            <?php echo CHtml::dropDownList('moduleId', $moduleKey, $modules, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="">Language</label>
                            <?php echo CHtml::dropDownList('language', $languageKey, $languages, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">File</label>
                            <?php echo CHtml::dropDownList('category', $categoryKey, $categories, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
                        </div>

                    </div>
                    <hr>

                    <span style='color:red'>Save before change!</span>
                    <?php echo CHtml::endForm(); ?>



                    <?php $i = 0; ?>

                    <?php echo CHtml::beginForm($this->createUrl('//translation/translate/save'), 'POST'); ?>
                    <?php echo CHtml::hiddenField('language', $languageKey); ?>
                    <?php echo CHtml::hiddenField('category', $categoryKey); ?>
                    <?php echo CHtml::hiddenField('moduleId', $moduleKey); ?>
                    <p>
                        If the value is empty, the message is considered as not translated.
                        Messages that no longer need translation will have their translations enclosed between a pair of '@@' marks.
                        Message string can be used with plural forms format. Check i18n section of the documentation for details.
                        <br/>
                        <br/>
                        For more informations about translation syntax see <a
                            href="http://www.yiiframework.com/doc/guide/1.1/en/topics.i18n#translation">Yii Framework Guide I18n</a>.

                    </p>

                    <p><?php echo CHtml::submitButton(Yii::t('TranslationModule.views_translate_index', 'Save'), array('class' => 'btn btn-primary')); ?></p>

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
                                    <pre style=""><?php print CHtml::encode($orginal); ?></pre>
                                </td>
                                <td style="width:50%;max-width:50%; padding:10px;"><?php echo CHtml::textArea('tid_' . md5($orginal), $translated, array('class' => 'form-control')); ?></td>
                            </tr>
                        <?php endforeach; ?>

                    </table>
                    <hr>
                    <p><?php echo CHtml::submitButton(Yii::t('TranslationModule.views_translate_index', 'Save'), array('class' => 'btn btn-primary')); ?></p>

                    <?php echo CHtml::endForm(); ?>

                </div>

            </div>

        </div>

    </div>
</div>    
