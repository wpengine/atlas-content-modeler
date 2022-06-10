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
`insert_model_entry()` will insert a model (post type) into the database. On success will return the
newly created `WP_Post` id. If an error occurs, a `WP_Error` object will be returned.

### Parameters
**model_slug** The content model slug. This is known as the **Model ID** when creating a model.

**field_data** An associative array of the model's field data. The key value should be the field's **API Identifier**.

**post_data** An optional associative array of `WP_Post` data. See [wp_insert_post](https://developer.wordpress.org/reference/functions/wp_insert_post/)
for possible post data.

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
****
