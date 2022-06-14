# Atlas Content Modeler CRUD

_description needed_

***

## insert_model_entry()
Insert a content model into the database.

### Namespace
```php
WPE\AtlasContentModeler\API
```

### Description
```
insert_model_entry( string $model_slug, array $field_data, array $post_data = [] )
```
`insert_model_entry()` will insert a model (post type) into the database. On success will return a `WP_Post` id or a `WP_Error` on failure.

### Parameters
- **model_slug** The content model slug. This is known as the **Model ID** when creating a model.
- **field_data** An associative array of the model's field data. The key value should be the field's **API Identifier**.
- **post_data** An optional associative array of `WP_Post` data. See [wp_insert_post](https://developer.wordpress.org/reference/functions/wp_insert_post/) for possible post data.

### Return
The `WP_Post` id on success or `WP_Error` on failure.

### Examples

The following examples with use a Rabbit model with a slug of `rabbit`. The Rabbit model has  the following three fields.
- Name `name` (text, required)
- Color `color` (text, required)
- Speed `speed` (numeric, int)

#### Example #1 Successful content model creation.
```php
use function WPE\AtlasContentModeler\API\insert_model_entry;

$model_slug = 'rabbit';
$field_data = [
	'name' => 'Peter',
	'color' => 'Brown',
	'speed' => 7,
];

$post_id = insert_model_entry( $model_slug, $field_data );

var_dump( $post_id );
```
Will result in the following
```
int(139)
```

#### Example #2 Unsuccessful content model creation.
```php
use function WPE\AtlasContentModeler\API\insert_model_entry;

$model_slug = 'rabbit';
$field_data = [
	'color' => 123,
	'speed' => 'not a number',
];

$post_id = insert_model_entry( $model_slug, $field_data );

if ( is_wp_error( $post_id ) ) {
	var_dump( $post_id->errors );
}
```
The `WP_Error` object will have errors for each field
```php
[
	'name' => [
		'Name is required',
	],
	'color' => [
		'Color must be valid text',
	],
	'speed' => [
		'Speed must be a valid number',
	],
]
```

## update_model_entry()
Update an existing content model.

### Namespace
```php
WPE\AtlasContentModeler\API
```

### Description
```
update_model_entry( int $post_id, array $field_data, array $post_data = [] )
```
`update_model_entry()` will update an existing content model (post type). On success will return the `WP_Post` id or a `WP_Error` on failure.

### Parameters
- **post_id** The content model ID.
- **field_data** An associative array of the model's field data. The key value should be the field's **API Identifier**.
- **post_data** An optional associative array of `WP_Post` data. See [wp_insert_post](https://developer.wordpress.org/reference/functions/wp_insert_post/) for possible post data.

### Return
The `WP_Post` id on success or `WP_Error` on failure.

### Examples

The following examples with use a Rabbit model with an existing ID of `3`. The Rabbit model has  the following three fields.
- Name `name` (text, required)
- Color `color` (text, required)
- Speed `speed` (numeric, int)

#### Example #1 Successful content model update.
```php
use function WPE\AtlasContentModeler\API\update_model_entry;

$model_id = 3;
$field_data = [
	'name' => 'Peter',
	'color' => 'White',
	'speed' => 9,
];

$post_id = update_model_entry( $model_id, $field_data );

var_dump( $post_id );
```
Will result in the following
```
int(139)
```

#### Example #2 Unsuccessful content model update.
```php
use function WPE\AtlasContentModeler\API\update_model_entry;

$model_id = 3;
$field_data = [
	'color' => 123,
	'speed' => 'not a number',
];

$post_id = update_model_entry( $model_id, $field_data );

if ( is_wp_error( $post_id ) ) {
	var_dump( $post_id->errors );
}
```
The `WP_Error` object will have errors for each field
```php
[
	'color' => [
		'Color must be valid text',
	],
	'speed' => [
		'Speed must be a valid number',
	],
]
```

## get_model()
Get a model schema.

### Namespace
```php
WPE\AtlasContentModeler\API
```

### Description
```
function get_model( string $model ): ?array
```
`get_model()` will retreive a model schema as an associative array. If the model does not exist, `null` will be returned.

### Parameters
- **model** The content model slug.

### Return
The model schema as an `array` or `null`.

### Examples

#### Example
```php
use function WPE\AtlasContentModeler\API\get_model;

$schema = get_model( 'rabit' );

var_dump( $schema );
```

Will result in the following
```
[
	'show_in_rest'    => true,
	'show_in_graphql' => true,
	'singular'        => 'Rabbit',
	'plural'          => 'Rabbits',
	'slug'            => 'rabbit',
	'api_visibility'  => 'private',
	'model_icon'      => 'dashicons-admin-post',
	'description'     => '',
	'fields'          => [
		[
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'text',
			'id'              => '1654892929464',
			'position'        => '0',
			'name'            => 'Name',
			'slug'            => 'name',
			'isRepeatable'    => false,
			'isTitle'         => true,
			'inputType'       => 'single',
			'required'        => true,
			'description'     => '',
			'minChars'        => '',
			'maxChars'        => '',
			'minRepeatable'   => '',
			'maxRepeatable'   => '',
		],
	],
]
```
