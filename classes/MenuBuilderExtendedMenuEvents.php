<?php
/*
 * Menu Builder Extended plugin
 * @package menu_builder_extended
 */

use Elgg\I18n;

class MenuBuilderExtendedMenuEvents {
	
	/**
	 * Adds the menu items to the menus managed by menu_builder - Revised
	 *
	 * @param \Elgg\Event $event 'register', "menu:{$menu_name}"
	 *
	 * @return MenuItems
	 */
	public static function registerAllMenu(\Elgg\Event $event) {
		$current_menu = $event->getParam('name');
		/* @var $return MenuItems */
		$return = $event->getValue();
		$return->fill([]); // need to reset as there should be no other way to add menu items
		
		$menu = new \ColdTrick\MenuBuilder\Menu($current_menu);
	
		// fix menu name if needed
		$lang_key = 'menu:' . elgg_get_friendly_title($current_menu) . ':header:default';
		if (!elgg_language_key_exists($lang_key)) {
			elgg()->translator->addTranslation(elgg_get_current_language(), [$lang_key => $current_menu]);
		}
	
		// add configured menu items
		$menu_items = $menu->getMenuConfig();
		// $menu_items = json_decode(elgg_get_plugin_setting("menu_{$current_menu}_config", 'menu_builder'), true);
	
		if (is_array($menu_items)) {
			foreach ($menu_items as $menu_item) {
				$can_add_menu_item = true;
					
				if (elgg_in_context('menu_builder_manage')) {
					$menu_item['menu_builder_menu_name'] = $current_menu;
				} else {
					$access_id = $menu_item['access_id'];
					unset($menu_item['access_id']);
					switch($access_id) {
						case ACCESS_PRIVATE:
							if (!elgg_is_admin_logged_in()) {
								$can_add_menu_item = false;
							}
							break;
						case \ColdTrick\MenuBuilder\Menu::ACCESS_LOGGED_OUT:
							if (elgg_is_logged_in()) {
								$can_add_menu_item = false;
							}
							break;
						case ACCESS_LOGGED_IN:
							if (!elgg_is_logged_in()) {
								$can_add_menu_item = false;
							}
							break;
					}
				}
					
				if (!$can_add_menu_item) {
					continue;
				}
				
				if (empty($menu_item['target'])) {
					unset($menu_item['target']);
				}
				
				// strip out deprecated use of [wwwroot] as menu items will be normalized by default
				$menu_item['href'] = str_replace('[wwwroot]', '', $menu_item['href']);
				
				// add global replacable action tokens
				$is_action = (bool) elgg_extract('is_action', $menu_item, false);
				unset($menu_item['is_action']);
				if ($is_action && !elgg_in_context('menu_builder_manage')) {
					$menu_item['is_action'] = true;
				}
				
				// open in lightbox
				$lightbox = (bool) elgg_extract('lightbox', $menu_item, false);
				unset($menu_item['lightbox']);
				if ($lightbox) {
					$menu_item['link_class'] = ['elgg-lightbox'];
				}
				
				if (empty($menu_item['href'])) {
					$menu_item['href'] = false;
				} else {
					$menu_item['href'] = self::replacePlaceholders($menu_item['href']);
				}

				// Revised: replace text
				$menu_item['text'] = elgg_echo($menu_item['text']);
				
				$return[] = \ElggMenuItem::factory($menu_item);
			}
		}
	
		// add 'new menu item' menu item
		if (elgg_in_context('menu_builder_manage')) {
			$return[] = \ElggMenuItem::factory([
				'name' => 'menu_builder_add',
				'icon' => 'plus',
				'text' => elgg_echo('menu_builder:edit_mode:add'),
				'href' => elgg_http_add_url_query_elements('ajax/view/menu_builder/edit_item', [
					'item_name' => 'menu_builder_add',
					'menu_name' => $current_menu,
				]),
				'link_class' => 'elgg-lightbox',
				'menu_builder_menu_name' => $current_menu,
				'priority' => time(),
			]);
		}
	
		return $return;
	}

	// /**
	//  * Makes menus managable if needed
	//  *
	//  * @param \Elgg\Event $event 'prepare', "menu:{$menu_name}"
	//  *
	//  * @return array
	//  */
	// public static function prepareAllMenu(\Elgg\Event $event) {
		
	// 	// update order
	// 	$ordered = [];
	// 	$return = $event->getValue();
		
	// 	if (isset($return['default'])) {
	// 		foreach ($return['default'] as $menu_item) {
	// 			$menu_item = self::orderMenuItem($menu_item, 2);
	// 			$priority = $menu_item->getPriority();
	// 			while (array_key_exists($priority, $ordered)) {
	// 				$priority++;
	// 			}
				
	// 			$ordered[$priority] = $menu_item;
	// 		}
		
	// 		ksort($ordered);
			
	// 		$return['default']->fill($ordered);
	// 	}
		
	// 	$menu = elgg_extract('default', $return, []);
	
	// 	// prepare menu items for edit
	// 	if (elgg_in_context('menu_builder_manage')) {
	// 		self::prepareMenuItemsEdit($menu->getItems());
	// 	}
	
	// 	return $return;
	// }
	
	// /**
	//  * Prepares menu items to be edited - Revised
	//  *
	//  * @param \ElggMenuItem[] $menu array of \ElggMenuItem objects
	//  *
	//  * @return void
	//  */
	// protected static function prepareMenuItemsEdit(array $menu): void {
	// 	foreach ($menu as $menu_item) {
	// 		// Revised: replace text
	// 		// $text = $menu_item->getText();
	// 		$text = elgg_echo($menu_item->getText());
			
	// 		$name = $menu_item->getName();
	// 		$menu_name = $menu_item->menu_builder_menu_name;
			
	// 		if ($name == 'menu_builder_add') {
	// 			continue;
	// 		}
			
	// 		// Revised: not sure why but it show duplicated edit/delete icons, so it was commendout
	// 		$text .= elgg_format_element('span', [
	// 			'title' => elgg_echo('edit'),
	// 			'class' => ['elgg-lightbox', 'mls', 'menu-builder-action'],
	// 			'data-colorbox-opts' => json_encode([
	// 				'href' => elgg_http_add_url_query_elements('ajax/view/menu_builder/edit_item', [
	// 					'item_name' => $name,
	// 					'menu_name' => $menu_name,
	// 				]),
	// 			]),
	// 		], elgg_view_icon('settings-alt'));
			
	// 		$text .= elgg_format_element('span', [
	// 			'title' => elgg_echo('delete'),
	// 			'class' => ['mls', 'menu-builder-action'],
	// 			'data-href' => elgg_generate_action_url('menu_builder/menu_item/delete', [
	// 				'item_name' => $name,
	// 				'menu_name' => $menu_name,
	// 			]),
	// 		], elgg_view_icon('delete'));

	// 		$menu_item->setText($text);
	// 		$menu_item->setHref(false);
	
	// 		$children = $menu_item->getChildren();
	// 		if ($children) {
	// 			self::prepareMenuItemsEdit($children);
	// 		}
	// 	}
	// }
	
	/**
	 * Replaces placeholders in a string with actual information
	 *
	 * @param string $text the text to replace items in
	 *
	 * @return string
	 */
	protected static function replacePlaceholders(string $text): string {
		$user = elgg_get_logged_in_user_entity();
				
		// fill in username/userguid
		if ($user) {
			$text = str_replace('[username]', $user->username, $text);
			$text = str_replace('[userguid]', $user->guid, $text);
		} else {
			$text = str_replace('[username]', '', $text);
			$text = str_replace('[userguid]', '', $text);
		}
		
		return $text;
	}
	
	// /**
	//  * Reorders menu item and adds an add button
	//  *
	//  * @param \ElggMenuItem $item  menu item
	//  * @param int           $depth depth of the menu item
	//  *
	//  * @return \ElggMenuItem
	//  */
	// protected static function orderMenuItem(\ElggMenuItem $item, int $depth): \ElggMenuItem {
	// 	$children = $item->getChildren();
	// 	if (empty($children)) {
	// 		return $item;
	// 	}
	
	// 	// sort children
	// 	$ordered_children = [];
	
	// 	foreach ($children as $child) {
	// 		$child = self::orderMenuItem($child, $depth + 1);
	
	// 		$child_priority = $child->getPriority();
	// 		while (array_key_exists($child_priority, $ordered_children)) {
	// 			$child_priority++;
	// 		}
			
	// 		$ordered_children[$child_priority] = $child;
	// 	}
		
	// 	ksort($ordered_children);
	
	// 	$item->setChildren($ordered_children);
	
	// 	return $item;
	// }
}
