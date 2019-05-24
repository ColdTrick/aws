<?php
/**
 * Main file of the plugin
 */

@include(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/lib/functions.php');

// register default elgg events
elgg_register_event_handler('init', 'system', 'aws_init');

/**
 * Called during system init
 *
 * @return void
 */
function aws_init() {

	// register events
	elgg_register_event_handler('delete', 'object', 'ColdTrick\AWS\Events::deleteObject');
	
	// plugin hooks
	elgg_register_plugin_hook_handler('upload:subtypes', 'aws:s3', 'ColdTrick\AWS\Plugins\File::registerSubtypesForUpload');
	elgg_register_plugin_hook_handler('cron', 'minute', 'ColdTrick\AWS\Cron::cleanupS3');
	elgg_register_plugin_hook_handler('cron', 'minute', 'ColdTrick\AWS\Cron::uploadFilesToS3');
}
