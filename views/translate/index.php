<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('TranslationModule.admin', 'Translation Editor'); ?></div>
    <div class="panel-body"><?php echo Yii::t('TranslationModule.base', 'Translation manager'); ?>

        <?php echo CHtml::beginForm($this->createUrl('//translation/translate'), 'GET'); ?>
        <?php echo CHtml::dropDownList('moduleId', $moduleKey, $modules, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
        <?php echo CHtml::dropDownList('language', $languageKey, $languages, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
        <?php echo CHtml::dropDownList('category', $categoryKey, $categories, array('class' => 'form-control', 'onChange' => 'this.form.submit();')); ?>
        <span style='color:red'>Save before change!</span>
        <?php echo CHtml::endForm(); ?>

        <hr>


        <?php $i = 0; ?>

        <?php echo CHtml::beginForm($this->createUrl('//translation/translate/save'), 'POST'); ?>
        <?php echo CHtml::hiddenField('language', $languageKey); ?>
        <?php echo CHtml::hiddenField('category', $categoryKey); ?>
        <?php echo CHtml::hiddenField('moduleId', $moduleKey); ?>
        <p> 
            If the value is empty, the message is considered as not translated.
            Messages that no longer need translation will have their translations enclosed between a pair of '@@' marks.
            Message string can be used with plural forms format. Check i18n section of the documentation for details.
            <br />
            <br />
            For more informations about translation syntax see <a href="http://www.yiiframework.com/doc/guide/1.1/en/topics.i18n#translation">Yii Framework Guide I18n</a>.

        </p>
        <hr>
        <p><?php echo CHtml::submitButton(Yii::t('base', 'Save')); ?></p>


        <table border="1" width="100%">
            <tr>
                <th style="width:50%;max-width:50%">Original (en)</th>
                <th style="width:50%;max-width:50%">Translated (<?php echo $language; ?>)</th>
            </tr>

            <?php foreach ($messages as $orginal => $translated) : ?>
                <tr style=''>
                    <?php $i++; ?>
                    <td style="width:50%;max-width:50%; padding:10px;"><pre style=""><?php print CHtml::encode($orginal); ?></pre></td>
                    <td style="width:50%;max-width:50%; padding:10px;"><?php echo CHtml::textArea('tid_' . md5($orginal), $translated, array('class' => 'form-control')); ?></td>
                </tr>
            <?php endforeach; ?>

        </table>
        <p><?php echo CHtml::submitButton(Yii::t('base', 'Save')); ?></p>

        <?php echo CHtml::endForm(); ?>

    </div>

</div>

