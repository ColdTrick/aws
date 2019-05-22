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

}
