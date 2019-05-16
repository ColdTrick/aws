<?php

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('aws:settings:api_key_id'),
	'name' => 'params[api_key_id]',
	'value' => $plugin->api_key_id,
]);

echo elgg_view_field([
	'#type' => 'password',
	'#label' => elgg_echo('aws:settings:api_key_secret'),
	'name' => 'params[api_key_secret]',
	'value' => $plugin->api_key_secret,
	'class' => ['elgg-input-text'],
]);
