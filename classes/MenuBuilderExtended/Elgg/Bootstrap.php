<?php
/*
 * Menu Builder Extended plugin
 * @package menu_builder_extended
 */

namespace MenuBuilderExtended\Elgg;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	const HANDLERS = [];
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		$this->initViews();
	}

	/**
	 * Init views
	 *
	 * @return void
	 */
	protected function initViews() {

		$action_path = elgg_get_plugins_path() . 'menu_builder_extended/actions';
		elgg_unregister_action('menu_builder/menu_item/edit');
    	elgg_register_action('menu_builder/menu_item/edit', "$action_path/menu_builder_extended/menu_item/edit.php");
				
	}

	
	/**
	 * {@inheritDoc}
	 */
	public function ready() {
		$events = $this->elgg()->events;		
		$managed_menus = menu_builder_get_managed_menus();
		
		foreach ($managed_menus as $menu_name) {
			$events->unregisterHandler('register', "menu:{$menu_name}", 'ColdTrick\MenuBuilder\Menus::registerAllMenu');
			$events->registerHandler('register', "menu:{$menu_name}", '\MenuBuilderExtendedMenuEvents::registerAllMenu', 9999);
			
			// $events->unregisterHandler('prepare', "menu:{$menu_name}", 'ColdTrick\MenuBuilder\Menus::prepareAllMenu');
			// $events->registerHandler('prepare', "menu:{$menu_name}", '\MenuBuilderExtendedMenuEvents::prepareAllMenu', 999);
		}
	}	
}
