<?php

namespace ColdTrick\AWS;

class Events {

	/**
	 * Listen to the delete of an \ELggObject to also remove it from AWS S3
	 *
	 * @param \Elgg\Event $event 'delete', 'object'
	 *
	 * @return void
	 */
	public static function deleteObject(\Elgg\Event $event) {
		
		$object = $event->getObject();
		if (!$object instanceof \ElggFile) {
			// only \ElggFile objects (and extensions) are supported
			return;
		}
		
		if (empty($object->aws_object_url)) {
			// not stored in AWS
			return;
		}
		
		// write a cleanup file in the plugin directory
		$plugin = elgg_get_plugin_from_id('aws');
		
		$file = new \ElggFile();
		$file->owner_guid = $plugin->guid;
		$file->setFilename("delete_queue/{$object->guid}.json");
		
		$file->open('write');
		$file->write(json_encode([
			'uri' => $object->aws_object_url,
		], JSON_PRETTY_PRINT));
		$file->close();
	}
}
