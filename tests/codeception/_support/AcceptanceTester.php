<?php

namespace translation;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \AcceptanceTester
{
    use _generated\AcceptanceTesterActions;

    /**
     * Define custom actions here
     */
    private function enableSpaceModule(AcceptanceTester $I)
    {
        $I->amAdmin();
        $I->wantToTest('the creation of a task list');
        $I->amGoingTo('install the calendar module for space 1');
        $I->enableModule(1, 'gallery');
        $I->amOnSpace1();
    }
}
