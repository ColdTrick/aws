<?php
/**
 * All helper functions are bundled here
 */

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\S3\ObjectUploader;
use Elgg\EntityDirLocator;
use Aws\S3\S3UriParser;
use Aws\Rekognition\RekognitionClient;

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
	
	try {
		return new S3Client([
			'credentials' => $credentials,
			'region' => $region,
			'version' => '2006-03-01',
			'scheme' => $scheme,
			'http' => [
				'connect_timeout' => 2,
				'timeout' => 5,
			],
		]);
	} catch (\Exception $e) {
		elgg_log(__METHOD__ . " failed to create a client: {$e->getMessage()}");
	}
	
	return false;
}

/**
 * Get a RekognitionClient for text/facial rekognition
 *
 * @return false|\Aws\Rekognition\RekognitionClient
 */
function aws_get_rekognition_client() {
	
	$credentials = aws_get_credentials();
	$region = elgg_get_plugin_setting('s3_region', 'aws');
	$scheme = elgg_get_plugin_setting('s3_scheme', 'aws', 'https');
	
	if (empty($credentials) || empty($region) || !in_array($scheme, ['http', 'https'])) {
		return false;
	}
	
	try {
		return new RekognitionClient([
			'credentials' => $credentials,
			'region' => $region,
			'version' => '2016-06-27',
			'scheme' => 'https',
			'http' => [
				'connect_timeout' => 2,
				'timeout' => 10,
				'verify' => $scheme === 'https',
			],
		]);
	} catch (\Exception $e) {
		elgg_log(__METHOD__ . " failed to create a client: {$e->getMessage()}");
	}
	
	return false;
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
	
	$key = aws_get_entity_key($file);
	if (empty($key)) {
		return false;
	}
	
	$bucket = elgg_get_plugin_setting('s3_bucket', 'aws');
	$s3client = aws_get_s3_client();
	
	if (empty($bucket) || empty($s3client)) {
		return false;
	}
	
	$uploader = new ObjectUploader(
		$s3client,
		$bucket,
		$key,
		$file->grabFile(),
		'private',
		[
			'params' => [
				'ContentType' => $file->getMimeType(),
			],
		]
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
 * Generate a key for use in S3
 *
 * @param ElggFile $entity the file to create the key for
 *
 * @return false|string
 */
function aws_get_entity_key(ElggFile $entity) {
	
	if ($entity->guid < 1) {
		return false;
	}
	
	// Store files in S3 under dir structure <bucket size>/guid.ext
	$dir_locator = new EntityDirLocator($entity->guid);
	$key = rtrim($dir_locator->getPath(), '/');
	
	$extension = pathinfo($entity->getFilenameOnFilestore(), PATHINFO_EXTENSION);
	if (!empty($extension)) {
		$key .= ".{$extension}";
	}
	
	return $key;
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
	
	$pr = aws_parse_s3_uri($uri);
	if (empty($pr)) {
		return false;
	}
	
	$s3client = aws_get_s3_client();
	if (empty($s3client)) {
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

/**
 * Parse an S3 uri to usable information
 *
 * @param string $uri the uri to parse
 *
 * @return false|array
 */
function aws_parse_s3_uri($uri) {
	
	if (empty($uri) || !is_string($uri)) {
		return false;
	}
	
	$parser = new S3UriParser();
	try {
		return $parser->parse($uri);
	} catch (Exception $e) {
		elgg_log(__METHOD__ . " parsing failed for URI '{$uri}': {$e->getMessage()}", 'WARNING');
	}
	
	return false;
}

/**
 * Get the supported subtyped for uploading to S3
 *
 * All returned subtypes are validated instances of an \ElggFile
 *
 * @return string[]
 */
function aws_get_supported_upload_subtypes() {
	$defaults = [
		'file',
	];
	
	$subtypes = elgg_trigger_plugin_hook('upload:subtypes', 'aws:s3', ['default' => $defaults], $defaults);
	if (empty($subtypes) || !is_array($subtypes)) {
		return [];
	}
	
	// validate that the given subtypes are an instanceof \ElggFile
	foreach ($subtypes as $index => $subtype) {
		$class = get_subtype_class('object', $subtype);
		if (empty($class)) {
			unset($subtypes[$index]);
			continue;
		}
		
		if ($class === \ElggFile::class || is_subclass_of($class, \ElggFile::class)) {
			// correct implementation
			continue;
		}
		
		// unsupported class
		unset($subtypes[$index]);
	}
	
	return array_values($subtypes);
}

/**
 * Detect text in ElggFile
 *
 * @param ElggFile $entity the file to scan (should be an image)
 * @param array    $params result set filters
 *
 * @return false|string[]|array
 */
function aws_detect_text(ElggFile $entity, array $params = []) {
	
	
	
	
	if (empty($entity->aws_object_url) || $entity->getSimpleType() !== 'image') {
		return false;
	}
	
	$rekog_client = aws_get_rekognition_client();
	if (empty($rekog_client)) {
		return false;
	}
	
	$pr = aws_parse_s3_uri($entity->aws_object_url);
	
	try {
		$result = $rekog_client->detectText([
			'Image' => [
				'S3Object' => [
					'Bucket' => elgg_extract('bucket', $pr),
					'Name' => elgg_extract('key', $pr),
				],
			],
		]);
	} catch (\Exception $e) {
		return false;
	}
	
	$texts = $result->get('TextDetections');
	if (empty($texts)) {
		return [];
	}
	
	var_dump($texts);
}
