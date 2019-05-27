<?php

$subtypes = aws_get_supported_upload_subtypes();
if (empty($subtypes)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$result = elgg_delete_metadata([
	'type_subtype_pairs' => [
		'object' => $subtypes,
	],
	'metadata_names' => [
		'aws_object_url',
	],
	'limit' => false,
]);

if ($result === false) {
	return elgg_error_response(elgg_echo('save:fail'));
}

return elgg_ok_response('', elgg_echo('save:success'));
