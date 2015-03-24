<div class="container">
    <div class="row">
        <div class="col-md-12">


            <div class="panel panel-default">
                <div class="panel-heading"><?php echo Yii::t('TranslationModule.views_translate_index', 'Translation Editor'); ?></div>
                <div class="panel-body">

                    <p><strong>You're no member of any translation space.</strong></p>

                    <p>Join as example the space 'DE' to get access to German translation files</p>
                    <br />
                    <p><?php echo CHtml::link('Go to Space overview', $this->createUrl('//directory/directory/spaces')); ?></p>
                </div>

            </div>
        </div>

    </div>
</div>
