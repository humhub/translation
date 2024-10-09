<?php

namespace humhub\modules\translation\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use Yii;

class ManageTranslations extends BasePermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'translation';

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_MEMBER,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_USER,
        Space::USERGROUP_GUEST,
    ];

    public function getTitle()
    {
        return Yii::t('TranslationModule.base', 'Confirm translations.');
    }

    public function getDescription()
    {
        return Yii::t('GalleryModule.base', 'Allows the user to directly manage translations.');
    }
}
