<?php

namespace ColdTrick\AWS;

use Elgg\EntityDirLocator;

class Cron {

	/**
	 * Cleanup files in AWS S3 bucket that have been removed from the community
	 *
	 * @param string $hook   'cron'
	 * @param string $type   'minute'
	 * @param mixed  $return current return value
	 * @param array  $params supplied params
	 *
	 * @return void
	 */
	public static function cleanupS3($hook, $type, $return, $params) {
		
		$plugin = elgg_get_plugin_from_id('aws');
		$dir_locator = new EntityDirLocator($plugin->guid);
		$path = elgg_get_data_path() . $dir_locator->getPath() . 'delete_queue/';
		if (!is_dir($path)) {
			return;
		}
		
		$s3client = aws_get_s3_client();
		if (empty($s3client)) {
			return;
		}
		
		echo "Starting AWS cleanup" . PHP_EOL;
		elgg_log("Starting AWS cleanup");
				
		$directory = new \DirectoryIterator($path);
		
		set_time_limit(0);
		$start = microtime(true);
		
		/* @var $file \DirectoryIterator */
		foreach ($directory as $file) {
			if (!self::timeLeft($start, 10)) {
				break;
			}
			
			if (!$file->isFile() || $file->getExtension() !== 'json') {
				continue;
			}
			
			$content = file_get_contents($file->getPathname());
			if (empty($content)) {
				unlink($file->getPathname());
				continue;
			}
			
			$content = json_decode($content, true);
			$uri = elgg_extract('uri', $content);
			if (empty($uri)) {
				unlink($file->getPathname());
				continue;
			}
			
			$pr = aws_parse_s3_uri($uri);
			if (empty($pr)) {
				unlink($file->getPathname());
				continue;
			}
			
			try {
				$s3client->deleteObject([
					'Bucket' => elgg_extract('bucket', $pr),
					'Key' => elgg_extract('key', $pr),
				]);
			} catch (\Exception $e) {
				
			}
			
			$exist = aws_get_object_by_uri($uri);
			if (empty($exist)) {
				// this is expected
				unlink($file->getPathname());
				continue;
			}
		}
		
		echo "Done with AWS cleanup" . PHP_EOL;
		elgg_log("Done with AWS cleanup");
	}
	
	/**
	 * Upload new files to S3
	 *
	 * @param string $hook   'cron'
	 * @param string $type   'minute'
	 * @param mixed  $return current return value
	 * @param array  $params supplied params
	 *
	 * @return void
	 */
	public static function uploadFilesToS3($hook, $type, $return, $params) {
		
		$subtypes = aws_get_supported_upload_subtypes();
		if (empty($subtypes)) {
			return;
		}
		
		// not used in this function, but only to check if correct config settings are done
		$s3client = aws_get_s3_client();
		if (empty($s3client)) {
			return;
		}
		
		echo "Starting AWS file upload" . PHP_EOL;
		elgg_log("Starting AWS file upload");
		
		$starttime = microtime(true);
		set_time_limit(0);
		
		$aws_marker = elgg_get_metastring_id('aws_object_url');
		$dbprefix = elgg_get_config('dbprefix');
		
		$files = elgg_get_entities([
			'type_subtype_pairs' => [
				'object' => $subtypes,
			],
			'limit' => false,
			'batch' => true,
			'wheres' => [
				"e.guid NOT IN (
					SELECT entity_guid
					FROM {$dbprefix}metadata
					WHERE name_id = {$aws_marker}
				)",
			],
		]);
		
		/* @var $file \ElggFile */
		foreach ($files as $file) {
			if (!self::timeLeft($starttime, 30)) {
				break;
			}
			
			aws_upload_file($file);
		}
		
		echo "Done with AWS file upload" . PHP_EOL;
		elgg_log("Done with AWS file upload");
	}
	
	/**
	 * Is there time left to run the script
	 *
	 * @param float $starttime   start time of execution
	 * @param int   $max_runtime max runtime in seconds
	 *
	 * @return bool
	 */
	protected static function timeLeft($starttime, $max_runtime) {
		$starttime = (float) $starttime;
		$max_runtime = (int) $max_runtime;
		
		return ((microtime(true) - $starttime) < $max_runtime);
	}
}