<?php
/**
 * Main file of the plugin
 */

// register default elgg events
elgg_register_event_handler('init', 'system', 'aws_init');

/**
 * Called during system init
 *
 * @return void
 */
function aws_init() {

}
