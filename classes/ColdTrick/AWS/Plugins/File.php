<?php

namespace ColdTrick\AWS\Plugins;

class File {

	/**
	 * Register the correct subtypes for uploading to aws
	 *
	 * @param string $hook   'upload:subtypes'
	 * @param string $type   'aws:s3'
	 * @param array  $return current return value
	 * @param array  $params supplied params
	 *
	 * @return void|array
	 */
	public static function registerSubtypesForUpload($hook, $type, $return, $params) {
		
		if (!elgg_is_active_plugin('file')) {
			return;
		}
		
		if (!elgg_get_plugin_setting('upload_files', 'aws')) {
			return;
		}
		
		$return[] = 'file';
		
		return $return;
	}
}
