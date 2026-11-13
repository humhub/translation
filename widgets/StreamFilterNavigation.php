<?php

namespace humhub\modules\translation\widgets;

use humhub\modules\content\widgets\stream\WallStreamFilterNavigation;
use humhub\modules\translation\stream\filters\ModuleIdFilter;
use humhub\widgets\filter\PickerFilterInput;
use Yii;

class StreamFilterNavigation extends WallStreamFilterNavigation
{
    public $view = '@content/widgets/stream/views/wallStreamFilterNavigation';

    public const FILTER_BLOCK_MODULE = 'module';

    public function initFilterBlocks()
    {
        parent::initFilterBlocks();

        $this->addFilterBlock(static::FILTER_BLOCK_MODULE, [
            'title' => Yii::t('TranslationModule.base', 'Module'),
            'sortOrder' => 100,
        ], static::PANEL_COLUMN_1);
    }

    protected function initFilters()
    {
        parent::initFilters();

        $this->addFilter([
            'id' => ModuleIdFilter::ID,
            'class' => PickerFilterInput::class,
            'picker' => ModulePicker::class,
            'category' => ModuleIdFilter::CATEGORY,
            'pickerOptions' => [
                'id' => 'stream-module-picker',
                'name' => 'filter_module_id',
            ],
        ], static::FILTER_BLOCK_MODULE);
    }
}
