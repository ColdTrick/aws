<?php
/**
 * All helper functions are bundled here
 */

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\S3\ObjectUploader;
use Elgg\EntityDirLocator;
use Aws\S3\S3UriParser;

/**
 * Get the aws credentials
 *
 * @return false|\Aws\Credentials\Credentials
 */
function aws_get_credentials() {
	
	$api_key = elgg_get_plugin_setting('api_key_id', 'aws');
	$api_secret = elgg_get_plugin_setting('api_key_secret', 'aws');
	if (empty($api_key) || empty($api_secret)) {
		return false;
	}
	
	return new Credentials($api_key, $api_secret);
}

/**
 * Get the S3Client for storage needs
 *
 * @return false|\Aws\S3\S3Client
 */
function aws_get_s3_client() {
	
	$credentials = aws_get_credentials();
	$region = elgg_get_plugin_setting('s3_region', 'aws');
	$scheme = elgg_get_plugin_setting('s3_scheme', 'aws', 'https');
	
	if (empty($credentials) || empty($region) || !in_array($scheme, ['http', 'https'])) {
		return false;
	}
	
	return new S3Client([
		'credentials' => $credentials,
		'region' => $region,
		'version' => '2006-03-01',
		'scheme' => $scheme,
	]);
}

/**
 * Upload a file to S3
 *
 * @param \ElggFile $file the file to upload
 *
 * @return bool
 */
function aws_upload_file(\ElggFile $file) {
	
	if (empty($file->guid) || !$file->getFilename()) {
		return false;
	}
	
	$bucket = elgg_get_plugin_setting('s3_bucket', 'aws');
	$s3client = aws_get_s3_client();
	
	if (empty($bucket) || empty($s3client)) {
		return false;
	}
	
	// Store files in S3 under dir structure <bucket size>/guid
	$dir_locator = new EntityDirLocator($file->guid);
	$key = rtrim($dir_locator->getPath(), '/');
	
	$uploader = new ObjectUploader(
		$s3client,
		$bucket,
		$key,
		$file->grabFile()
	);
	
	try {
		/* @var $result Aws\Result */
		$result = $uploader->upload();
		
		$url = $result->get('ObjectURL');
		
		// store s3 location with the file
		$file->aws_object_url = $url;
	} catch (\Exception $e) {
		return false;
	}
	
	return true;
}

/**
 * Try to get an object from AWS S3 by GUID
 *
 * @param int $guid the guid to fetch
 *
 * @return false|\Aws\Result
 */
function aws_get_object_by_guid($guid) {
	
	$guid = (int) $guid;
	if ($guid < 1) {
		return false;
	}
	
	$s3client = aws_get_s3_client();
	$bucket = elgg_get_plugin_setting('s3_bucket', 'aws');
	if (empty($s3client) || empty($bucket)) {
		return false;
	}
	
	$dir_locator = new EntityDirLocator($guid);
	$key = rtrim($dir_locator->getPath(), '/');
	
	try {
		return $s3client->getObject([
			'Bucket' => $bucket,
			'Key' => $key,
		]);
	} catch (\Exception $e) {
		elgg_log(__METHOD__ . " failed for GUID '{$guid}': {$e->getMessage()}");
	}
	
	return false;
}

/**
 * Try to get an object from AWS S3 by URI
 *
 * @param string $uri the url to fetch
 *
 * @return false|\Aws\Result
 */
function aws_get_object_by_uri($uri) {
	
	if (empty($uri) || !is_string($uri)) {
		return false;
	}
	
	$s3client = aws_get_s3_client();
	if (empty($s3client)) {
		return false;
	}
	
	$parser = new S3UriParser();
	try {
		$pr = $parser->parse($uri);
	} catch (Exception $e) {
		elgg_log(__METHOD__ . " parsing failed for URI '{$uri}': {$e->getMessage()}", 'WARNING');
		return false;
	}
	
	try {
		return $s3client->getObject([
			'Bucket' => elgg_extract('bucket', $pr),
			'Key' => elgg_extract('key', $pr),
		]);
	} catch (\Exception $e) {
		elgg_log(__METHOD__ . " failed for URI '{$uri}': {$e->getMessage()}");
	}
	
	return false;
}
