<?php
use \LimeSurvey\Menu\Menu;
use \LimeSurvey\Menu\MenuItem;

class MipeAdminContent extends \LimeSurvey\PluginManager\PluginBase {
    protected $storage = 'DbStorage';

    static protected $description = 'Customize the admin behavior and content';
    static protected $name = 'MipeAdminContent';

    public function init() {
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeDeactivate');
        $this->subscribe('beforeAdminMenuRender');
    }

    public function saveSettings($settings)
    {
        foreach ($settings as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function beforeActivate()
    {
        PluginMigration::install();
    }

    public function beforeDeactivate()
    {
        PluginMigration::uninstall();
    }

    public function beforeAdminMenuRender()
    {
        $event = $this->getEvent();
        $buttonTestOptions = [
            'buttonId' => 'editais-button',
            'label' => 'Editais',
            // 'href' => 'https://limesurvey.org',
            'isDropDown' => true,
            'iconClass' => 'fa fa-link',
            // 'openInNewTab' => true,
            'isPrepended' => false,
            'tooltip' => 'Entidades MIPE',
            'menuItems' => array(
                new MenuItem(null),
                new MenuItem(null),
                new MenuItem(array('isDivider' => true)),
                new MenuItem(null)
            )
        ];

        $menuTestButton = new Menu($buttonTestOptions);
        $event->append('extraMenus', [$menuTestButton]);
  }
}