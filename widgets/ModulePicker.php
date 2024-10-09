<?php

namespace humhub\modules\translation\widgets;

use humhub\modules\translation\models\BasePath;
use humhub\modules\ui\form\widgets\BasePicker;

class ModulePicker extends BasePicker
{
    // Workaround since picker currently does not support non ajax results
    public $minInput = 3000;

    public function init()
    {
        $this->defaultResults = BasePath::getModuleIds();
    }

    /**
     * Used to retrieve the option text of a given $item.
     *
     * @param \yii\db\ActiveRecord $item selected item
     * @return string item option text
     */
    protected function getItemText($item)
    {
        return $item;
    }

    protected function getItemKey($item)
    {
        return $item;
    }

    protected function getData()
    {
        $result = parent::getData();
        // Workaround since picker currently does not support non ajax results
        $result['input-too-short'] = '';
        return $result;
    }

    /**
     * Used to retrieve the option image url of a given $item.
     *
     * @param \yii\db\ActiveRecord $item selected item
     * @return string|null image url or null if no selection image required.
     */
    protected function getItemImage($item)
    {
        return null;
    }
}
