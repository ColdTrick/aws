<?php

namespace ColdTrick\AWS\Plugins;

class File {

	/**
	 * Register the correct subtypes for uploading to aws
	 *
	 * @param \Elgg\Hook $hook 'upload:subtypes', 'aws:s3'
	 *
	 * @return void|array
	 */
	public static function registerSubtypesForUpload(\Elgg\Hook $hook) {
		
		if (!elgg_is_active_plugin('file')) {
			return;
		}
		
		if (!elgg_get_plugin_setting('upload_files', 'aws')) {
			return;
		}
		
		$return = $hook->getValue();
		
		$return[] = 'file';
		
		return $return;
	}
}
