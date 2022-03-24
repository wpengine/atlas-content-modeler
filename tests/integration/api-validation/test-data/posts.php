<?php
/**
 * Saves mock post data for processing
 */
function create_test_posts( $test_class ) {
	$ids = array();

	$ids['public_post_id'] = $test_class->factory->post->create(
		array(
			'post_title'   => 'Test dog',
			'post_content' => 'Hello dog',
			'post_status'  => 'publish',
			'post_type'    => 'public',
		)
	);

	$ids['public_fields_post_id'] = $test_class->factory->post->create(
		array(
			'post_title'   => 'Test dog with fields',
			'post_content' => 'Hello dog with fields',
			'post_status'  => 'publish',
			'post_type'    => 'public-fields',
		)
	);

	populate_post( $ids['public_fields_post_id'], 'public-fields', $test_class );

	$ids['draft_public_post_id'] = $test_class->factory->post->create(
		array(
			'post_title'   => 'Draft dog',
			'post_content' => 'This dog has a status of draft',
			'post_status'  => 'draft',
			'post_type'    => 'public',
		)
	);

	$ids['private_post_id'] = $test_class->factory->post->create(
		array(
			'post_title'   => 'Test cat',
			'post_content' => 'Hello cat',
			'post_status'  => 'publish',
			'post_type'    => 'private',
		)
	);

	$ids['private_fields_post_id'] = $test_class->factory->post->create(
		array(
			'post_title'   => 'Test dog with fields',
			'post_content' => 'Hello dog with fields',
			'post_status'  => 'publish',
			'post_type'    => 'private-fields',
		)
	);

	populate_post( $ids['private_fields_post_id'], 'private-fields', $test_class );

	return $ids;
}

function populate_post( $post_id, $model, $test_class ) {
	$ids = array();

	$ids[ $model . '_image_id' ] = $test_class->factory->attachment->create(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => 'Image Attachment',
		)
	);

	$ids[ $model . '_pdf_id' ] = $test_class->factory->attachment->create(
		array(
			'post_mime_type' => 'application/pdf',
			'post_title'     => 'PDF Attachement',
		)
	);

	update_post_meta( $post_id, 'singleLine', 'This is single line text' );
	update_post_meta( $post_id, 'singleLineRequired', 'This is required single line text' );
	update_post_meta( $post_id, 'singleLineTextRepeater', [ 'This is one line of repeater text', 'This is another line of repeater text' ] );
	update_post_meta( $post_id, 'singleLineLimited', 'This is single line text on a limited field' );
	update_post_meta( $post_id, 'multiLine', 'This is multi-line text' );
	update_post_meta( $post_id, 'richText', 'This is a rich text field' );
	update_post_meta( $post_id, 'richTextRepeatable', [ '<p>First</p>', '<p>Second</p>' ] );
	update_post_meta( $post_id, 'numberInteger', '42' );
	update_post_meta( $post_id, 'numberIntegerRepeat', [ 0, 1, 2 ] );
	update_post_meta( $post_id, 'numberDecimal', '3.14' );
	update_post_meta( $post_id, 'numberIntergerRequired', '13' );
	update_post_meta( $post_id, 'numberIntegerLimited', '3' );
	update_post_meta( $post_id, 'date', '2012/02/13' );
	update_post_meta( $post_id, 'dateRepeatable', [ '2021/02/13', '2021/02/14' ] );
	update_post_meta( $post_id, 'dateRequired', '2021/02/13' );
	update_post_meta( $post_id, 'media', $ids[ $model . '_image_id' ] );
	update_post_meta( $post_id, 'mediaRepeat', [ 1, 2, 3 ] );
	update_post_meta( $post_id, 'mediaRequired', $ids[ $model . '_image_id' ] );
	update_post_meta( $post_id, 'mediaPDF', $ids[ $model . '_pdf_id' ] );
	update_post_meta( $post_id, 'boolean', 'false' );
	update_post_meta( $post_id, 'booleanRequired', 'true' );
	update_post_meta( $post_id, 'multiSingle', [ 'kiwi' ] );
	update_post_meta( $post_id, 'multipleMulti', [ 'apple', 'banana' ] );
	update_post_meta( $post_id, 'featured', $ids[ $model . '_image_id' ] );
	update_post_meta( $post_id, '_thumbnail_id', $ids[ $model . '_image_id' ] );

	$image_meta = array(
		'width'  => 1000,
		'height' => 1000,
		'file'   => '2021/06/image.png',
		'sizes'  => array(
			'medium' => array(
				'file'      => 'image-300x300.png',
				'width'     => 300,
				'height'    => 300,
				'mime-type' => 'image/png',
			),
		),
	);

	update_post_meta( $ids[ $model . '_image_id' ], '_wp_attachment_metadata', $image_meta );
	update_post_meta( $ids[ $model . '_image_id' ], '_wp_attachment_image_alt', 'This is alt text' );
	update_post_meta( $ids[ $model . '_image_id' ], '_wp_attached_file', '2021/06/image.png' );
}
