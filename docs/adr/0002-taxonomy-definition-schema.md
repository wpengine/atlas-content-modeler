1: Add schema for storing user-provided taxonomy definitions
=====================================================

Date: 2021-06-23

Context
-------

- Developers need to create taxonomies for models so that publishers can organize their content.
- We need to store the user-provided taxonomy details so that we can register the taxomonies at runtime and associate them with the desired models.

Decision
--------

We will store taxonomy definitions in the `wp_options` table using the `atlas_content_modeler_taxonomies` option name. The definitions will be stored as an associative array, where the array keys are the model's slug. Example:
```
[
	'course' => [
		'singular' => 'Course', // string(50). Required.
		'plural' => 'Courses', // string(50). Required.
		'slug' => 'course', // string(32) - cannot exceed 32 chars, see register_taxonomy(). Required.
		'hierarchical' => false, // boolean - default false, see register_taxonomy()
		'types' => [ 'recipe' ], // array - post type slugs separated by comma. Required.
		'api_visibility' => 'private', // string - 'public' or 'private'. default 'private'. Required.
		'show_in_rest' => true, // boolean
		'show_in_graphql' => true, // boolean
	],
	'cuisine' => [
		'singular' => 'Cuisine',
		'plural' => 'Cuisines',
		'slug' => 'cuisine',
		'hierarchical' => true,
		'types' => [ 'recipe', 'restaurant' ],
		'api_visibility' => 'private',
		'show_in_rest' => true,
		'show_in_graphql' => true,
	],
];
```

The `wp_options` table and Options API are available in WordPress, and we use them already to store our model information. It's reasonably performant at scale, doesn't require us to maintain custom tables and CRUD APIs, and is easy to change later if necessary.


Consequences
------------

- Same pattern we use for models. Tested and familiar.
- Potential for memcache issues at scale under certain conditions. [Reference](https://10up.com/blog/2017/wp-options-table/).
- Does not increase PHP-based API surface in our application.
- We could easily migrate to any of the alternatives listed below if necessary.

Alternatives
------------

These alternatives were evaluated and rejected:

- Creating custom tables to store the taxonomy definitions. Doing this requires we also create custom CRUD methods for easily interacting with the custom tables. At this time, there are no significant benefits to using a custom table and custom CRUD methods.
- Storing the definitions as a custom post type in the `wp_posts` table. All the post APIs in WordPress are more complicated than the `{get/update/delete}_option` APIs. The shape of `wp_posts` doesn't fit our needs, which would require us to store the data as an array or JSON blob in `post_content` or similar. To make that easier to work with, we would also need a set of CRUD methods.
