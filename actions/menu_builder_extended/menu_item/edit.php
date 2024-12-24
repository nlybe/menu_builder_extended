<?php
/*
 * Menu Builder Extended plugin
 * @package menu_builder_extended
 */


$menu_name = get_input('menu_name');
if (!in_array($menu_name, menu_builder_get_managed_menus())) {
	return elgg_error_response(elgg_echo('menu_builder:actions:edit:error:input'));
}

$filter = true;
if (elgg_get_plugin_setting('htmlawed_filter', 'menu_builder') == 'no') {
	$filter = false;
}

// Revised
$text = get_input('text', null, $filter);
$identifier = 'menu_builder:menu_item:';
$lang_key = str_starts_with($text, $identifier) ? $text : 'menu_builder:menu_item:' . strtolower(str_replace(' ', '_', $text));
if (!elgg_language_key_exists($lang_key)) {
	elgg()->translator->addTranslation(elgg_get_current_language(), [$lang_key => $text]);
}

// add a default menu item
$menu = new \ColdTrick\MenuBuilder\Menu($menu_name);
$menu->addMenuItem([
	'name' => get_input('name'),
	'text' => $lang_key,	// Revised
	'href' => get_input('href', null, $filter),
	'icon' => get_input('icon', null),
	'access_id' => (int) get_input('access_id', ACCESS_PUBLIC),
	'target' => get_input('target'),
	'is_action' => get_input('is_action', false),
	'lightbox' => get_input('lightbox', false),
	'priority' => get_input('priority', time()),
	'parent_name' => get_input('parent_name'),
]);

return elgg_ok_response('', elgg_echo('menu_builder:actions:edit:success'));
