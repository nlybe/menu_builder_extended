<?php
/*
 * Menu Builder Extended plugin
 * @package menu_builder_extended
 */

use MenuBuilderExtended\Elgg\Bootstrap;

return [
    'plugin' => [
        'name' => 'Menu Builder Extended',
		'version' => '5.2',
		'dependencies' => [
			'menu_builder' => [],
		],
	],	
    'bootstrap' => Bootstrap::class,
];
