<?php

require_once(__DIR__ . '/lib/functions.php');

return [
	'settings' => [
		's3_scheme' => 'https',
	],
	'actions' => [
		'aws/admin/reset_upload' => [
			'access' => 'admin',
		],
	],
	'events' => [
		'delete' => [
			'object' => [
				'ColdTrick\AWS\Events::deleteObject' => [],
			],
		],
	],
	'hooks' => [
		'cron' => [
			'minute' => [
				'ColdTrick\AWS\Cron::cleanupS3' => [],
				'ColdTrick\AWS\Cron::uploadFilesToS3' => [],
			],
		],
		'upload:subtypes' => [
			'aws:s3' => [
				'ColdTrick\AWS\Plugins\File::registerSubtypesForUpload' => [],
			],
		],
	],
];
