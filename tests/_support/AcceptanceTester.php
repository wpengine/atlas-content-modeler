<?php


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
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Visit WPEngine Content Edit Model page.
     *
     * @param string $id The content model id.
     */
    public function amOnWPEngineEditContentModelPage($id)
    {
        $this->amOnWPEngineContentModelPage("&view=edit-model&id={$id}");
    }

    /**
     * Visit WPEngine Create Content Model page.
     */
    public function amOnWPEngineCreateContentModelPage()
    {
        $this->amOnWPEngineContentModelPage('&view=create-model');
    }

    /**
     * Visit WPEngine Content Model page.
     *
     * @param string $params Optional query parameter string.
     */
    public function amOnWPEngineContentModelPage($params = '')
    {
        $path = '/wp-admin/admin.php?page=atlas-content-modeler';

        if ( $params ) {
            $path .= $params;
        }

        $this->amOnPage($path);
    }

    /**
     * Create a Content Model.
     *
     * @param string $singular    Singular content model name.
     * @param string $plural      Plural content model name.
     * @param string $description Content model description.
     */
    public function haveContentModel($singular, $plural, $description = '')
    {
        $this->amOnPage('/wp-admin/admin.php?page=atlas-content-modeler&view=create-model');
        $this->wait(1);

        $this->fillField(['name' => 'singular'], $singular);
        $this->fillField(['name' => 'plural'], $plural);

        if ($description) {
            $this->fillField(['name' => 'description'], $description);
        }

        $this->click('.card-content button.primary');
    }
}
