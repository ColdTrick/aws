<?php

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\S3\ObjectUploader;

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

// Simple Storage Service (S3) config
$s3_config = elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('aws:settings:s3:bucket'),
	'name' => 'params[s3_bucket]',
	'value' => $plugin->s3_bucket,
]);

$s3_config .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('aws:settings:s3:region'),
	'name' => 'params[s3_region]',
	'value' => $plugin->s3_region,
	'options_values' => [
		'' => elgg_echo('aws:settings:s3:region:select'),
		'us-east-2' => elgg_echo('aws:settings:s3:region:us-east-2'),
		'us-east-1' => elgg_echo('aws:settings:s3:region:us-east-1'),
		'us-west-1' => elgg_echo('aws:settings:s3:region:us-west-1'),
		'us-west-2' => elgg_echo('aws:settings:s3:region:us-west-2'),
		'ap-east-1' => elgg_echo('aws:settings:s3:region:ap-east-1'),
		'ap-south-1' => elgg_echo('aws:settings:s3:region:ap-south-1'),
		'ap-northeast-3' => elgg_echo('aws:settings:s3:region:ap-northeast-3'),
		'ap-northeast-2' => elgg_echo('aws:settings:s3:region:ap-northeast-2'),
		'ap-southeast-1' => elgg_echo('aws:settings:s3:region:ap-southeast-1'),
		'ap-southeast-2' => elgg_echo('aws:settings:s3:region:ap-southeast-2'),
		'ap-northeast-1' => elgg_echo('aws:settings:s3:region:ap-northeast-1'),
		'ca-central-1' => elgg_echo('aws:settings:s3:region:ca-central-1'),
		'cn-north-1' => elgg_echo('aws:settings:s3:region:cn-north-1'),
		'cn-northwest-1' => elgg_echo('aws:settings:s3:region:cn-northwest-1'),
		'eu-central-1' => elgg_echo('aws:settings:s3:region:eu-central-1'),
		'eu-west-1' => elgg_echo('aws:settings:s3:region:eu-west-1'),
		'eu-west-2' => elgg_echo('aws:settings:s3:region:eu-west-2'),
		'eu-west-3' => elgg_echo('aws:settings:s3:region:eu-west-3'),
		'eu-north-1' => elgg_echo('aws:settings:s3:region:eu-north-1'),
		'sa-east-1' => elgg_echo('aws:settings:s3:region:sa-east-1'),
	],
]);

$s3_config .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('aws:settings:s3:scheme'),
	'name' => 'params[s3_scheme]',
	'default' => 'http',
	'value' => 'https',
	'checked' => $plugin->s3_scheme !== 'http',
]);

echo elgg_view_module('inline', elgg_echo('aws:settings:s3'), $s3_config);
