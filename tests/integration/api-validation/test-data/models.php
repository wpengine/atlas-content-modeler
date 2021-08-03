<?php

/**
 * Sample model data for testing
 */

require_once __DIR__ . '/fields.php';

return array(
    "public" => array(
        "show_in_rest" => true,
        "show_in_graphql" => true,
        "singular" => "Public",
        "plural" => "Publics",
        "slug" => "public",
        "api_visibility" => "public",
        "model_icon" => "dashicons-admin-post",
        "description" => "A public content model",
        "fields" => array(),
    ),
    "publicFields" => array(
        "show_in_rest" => true,
        "show_in_graphql" => true,
        "singular" => "Public-Fields",
        "plural" => "Publics-Fields",
        "slug" => "publicFields",
        "api_visibility" => "public",
        "model_icon" => "dashicons-admin-post",
        "description" => "A public content model with fields",
        "fields" => get_test_fields( 'publicFields' ),
    ),
    "private" => array(
        "show_in_rest" => true,
        "show_in_graphql" => true,
        "singular" => "Private",
        "plural" => "Privates",
        "slug" => "private",
        "api_visibility" => "private",
        "model_icon" => "dashicons-admin-post",
        "description" => "A private content model",
        "fields" => array(),
    )
);
