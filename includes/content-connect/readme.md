# ACM Content Connect

> Library for Atlas Content Modeler that enables direct relationships for posts to posts.

Based on the [10up/wp-content-connect](https://github.com/10up/wp-content-connect) library and heavily modified for use in the [Atlas Content Modeler](https://github.com/wpengine/atlas-content-modeler) WordPress plugin.

## Installation and Usage

### Composer install

First, you'll need to add this repository to your composer config.

```sh
composer config repositories.acm-content-connect vcs https://github.com/wpengine/acm-content-connect.git
```

Or, directly in `composer.json`:

```
  "repositories": [
      {
          "type": "vcs",
          "url": "https://github.com/wpengine/acm-content-connect.git"
      }
  ]
```

Now require the package.

```sh
composer require wpengine/acm-content-connect:dev-main
```

This will install WP Content Connect to your `vendor` folder and allow you to to use it as a library by calling `\WPE\AtlasContentModeler\ContentConnect\Plugin::instance();` from your code.


## Defining Relationships
Register relationships on the `acm_content_connect_init` action. Relationships can only be defined after any post types they utilize are defined. The `acm_content_connect_init` action is fired on the WordPress `init` action at priority 100, so any related post types must be registered prior to this. Additionally, when registering a relationship, you must specify a `name`. Name enables multiple distinct relationships between the same object types. For instance, you could have a relationship for the `project` post type with a `name` of `researchers` to indicate that any entry in the "researchers" relationship is a researcher for the project. You could then define another relationship for the `project` post type with a name of `backers` to indicate that any entry in the "backers" relationship contributes financially to the post. In this example, `researchers` and `backers` have
the same post type, so the `name` is the only ditingusing feature of each relationship.

### `define_post_to_post( $from, $to, $name, $args = array() )`
This method defines a post to post relationship between two post types, `$from` and `$to`.

#### Parameters:

`$from` (String) First post type in the relationship

`$to` (String|Array) Second post type(s) in the relationship

`$name` (String) Unique name for this relationship, used to distinguish between multiple relationships between the same post types

`$args` (Array) Array of options for the relationship

#### Args:

Optional.

`is_bidirectional` (Boolean) Should this relationship be queryable from either side of the relationship? Defaults to `false`.

You may also supply arguments for each side of the relationship through the `from` and `to` top level keys. Options for each direction are as follows:

- `enable_ui` (Bool) - Should the UI be enabled for the current side of this relationship
- `sortable` (Bool) - Should the relationship be sortable for the current side of this relationship
- `labels` (Array) - Labels used in the UI for the relationship. Currently only expects one value, `name` (String)

#### Return Value

This method returns an instance of `\WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost` specific to this relationship. The object can then be used to manage related items manually, if required. See the [Managing Relationships](https://github.com/wpengine/acm-content-connect#managing-relationships) section below.

Example:

```php
function my_define_relationships( $registry ) {
    $args = array(
        'is_bidirectional' => true,
        'from' => array(
            'enable_ui' => true,
            'sortable' => true,
            'labels' => array(
                'name' => 'Related Tires',
            ),
        ),
        'to' => array(
            'enable_ui' => false,
            'sortable' => false,
            'labels' => array(
                'name' => 'Related Cars',
            ),
        ),
    );

    $relationship = $registry->define_post_to_post( 'car', 'tire', 'car-tires', $args );
}
add_action( 'wpengine_content_connect_init', 'my_define_relationships' );

```


### Sortable Relationships
Relationships can optionally support sortable related items. Order can be stored independently for both sides of a relationship. For example, if you have cars and tires, you may have a car that has 5 related tires, and if you wanted to sort the tires, you do so from the car page. You could then go to one of the related tires, and order all of the cars it is related to separately.

Since you can manage this relationship from both post types in the relationship, if you added a tire from the car page, and you had relationship data previously stored on the tire, the NEW car in the relationship will still show up in query results, at the very end (after all of your other pre-ordered data).


## Query Integration

Querying for relationships is enabled via a new `acm_relationship_query` parameter for `WP_Query`. The format for `acm_relationship_query` is very similar to `tax_query`.

A valid relationship query segment **requires** `name` and `related_to_post`. As many relationship segments as necessary can be combined to create a specific set of results, and can be combined using an `AND` or `OR` relation.

#### Top Level Args:

- `relation` (String) Can be either `AND` (default) or `OR`. How all of the segments in the relationship should be combined.

#### Segment Args:

- `name` (String) The unique name for the relationship you are querying. Should match a `name` from registering relationships.
- `related_to_post` (Int) Find items in the relationship related to this post ID.

Example:

```php
$query = new WP_Query( array(
    'post_type' => 'post',
    'acm_relationship_query' => array(
        'relation' => 'AND', // AND is default
        array(
            'related_to_post' => 25,
            'name' => 'related',
        )
    ),
) );
```

Currently, querying for multiple post types in WP_Query may not work as expected. When using relationship queries, make sure to only have one `post_type` value in WP_Query.

#### Order By

For relationships where sorting is disabled, all of the default WP_Query `orderby` options are supported.
In addition to default `orderby` options, if sorting is enabled for a relationship, an additional orderby parameter `relationship` is supported.
When using `relationship` as the orderby value, the order is always `ASC` and you must adhere to the following `WP_Query` restrictions:

- Compound relationship queries are not allowed - only one segment may be added to the query

For example, this is fine:

```php
'acm_relationship_query' => array(
    array(
        'related_to_post' => 25,
        'name' => 'related',
    ),
),
'orderby' => 'relationship',
```

while this will not work (orderby will be ignored):
```php
'acm_relationship_query' => array(
    array(
        'related_to_post' => 25,
        'name' => 'related',
    ),
    array(
        'related_to_post' => 15,
        'name' => 'related',
    ),
),
'orderby' => 'relationship',
```

## Managing Relationships

**DO NOT** try and work directly with the database tables. Instead, work with the following API methods. The underlying implementations may need to change from time to time, but the following methods should continue to function if the underlying implementations need to change.

These methods are available on the relationship objects returned when defining the relationship. Make sure to call these methods on the specific relationship object you are defining a relationship for, as these methods are specific to the relationship context (they are aware of the `name` of the relationship, as well as the post types in the relationship).

If you don't already have a relationship object, you can get one from the registry using
`Registry->get_post_to_post_relationship()`.

### `Registry->get_post_to_post_relationship( $cpt1, $cpt2, $name )`
Returns the relationship object between the two post types with the provided name. For one way relationships, post type argument order must match the order was used to define the relationship.

#### Parameters:

`$cpt1` (String) The first post type in the relationship

`$cpt2` (String) The second post type in the relationship

`$name` (String) The name of the relationship, as passed to define_post_to_post_relationship

#### Example:

```php
$registry = \WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->get_registry();

// Gets the car to tire relationship defined in the example above
$relationship = $registry->get_post_to_post_relationship( 'car', 'tire', 'car-tires' );
```

### `PostToPost->add_relationship( $pid1, $pid2 )`
This method adds a relationship between one post and another, in a post to post relationship. When calling this method, the order of IDs passed is not important.

#### Parameters:

`$pid1` (Int) The ID of the first post in the relationship

`$pid2` (Int) The ID of the second post in the relationship

#### Example:

```php
// $relationship is the return value from ->define_post_to_post()
$relationship->add_relationship( 1, 2 ); // Adds a relationship between post ID 1 and post ID 2
```

### `PostToPost->delete_relationship( $pid1, $pid2 )`
This methods deletes a relationship between one post and another, in a post to post relationship. When calling this method, the order of IDs passed is not important.

#### Parameters:

`$pid1` (Int) The ID of the first post in the relationship. Does **not** need to be in the same order as the relationship was added.

`$pid2` (Int) The ID of the second post in the relationship. Does **not** need to be in the same order as the relationship was added.

#### Example:
```php
// $relationship is the return value from ->define_post_to_post()
// Note that the example above added these in the reverse order, but the relationship is still deleted
$relationship->delete_relationship( 2, 1 ); // Deletes the relationship between post ID 1 and post ID 2.
```

### `PostToPost->replace_relationships( $post_id, $related_ids )`
Replaces existing relationships for the post to post relationship. Any relationship that is present in the database but not in $related_ids will no longer be related.

#### Parameters:

`$post_id` (Int) The ID of the post we are replacing relationships from.

`$related_ids` (Array) An array of Post IDs of items related to $post_id

#### Example:

Post ID 5 is related to posts 2, 3, 6, 7, 8

```php
// $relationship is the return value from ->define_post_to_post()
$relationship->replace_relationships( 5, array( 2, 3, 6, 7, 8 ) );
```

### `PostToPost->save_sort_data( $object_id, $ordered_ids )`
For a relationship with sorting enabled, this saves the order of the posts for a single direction of the relationship.

#### Parameters:

`$object_id` (Int) The Post ID that we are ordering from. If we were ordering 5 tires for a single car, this would be the car ID.

`$ordered_ids` (Array) An array of Post IDs, in the order they should be sorted. If we were ordering 5 tires for a single car, this is the ordered tire IDs.

#### Example:

Car ID 5 has five related tires, that should be ordered 7, 6, 3, 8, 2

```php
// $relationship is the return value from ->define_post_to_post()
$relationship->save_sort_data( 5, array( 7, 6, 3, 8, 2 ) );
```
