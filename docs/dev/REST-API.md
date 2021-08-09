# Fetching Atlas Content Modeler Entries with the WordPress REST API

Atlas Content Modeler uses the [WordPress Core REST API](https://developer.wordpress.org/rest-api/) to expose model entries and field data.

## Private vs Public models

Models have private API visibility by default. You must authenticate with a plugin such as [Basic Auth](https://github.com/WP-API/Basic-Auth) to see REST responses for private model data.

To expose model data without authentication, edit the model at Content Modeler → Content Models and change its API Visibility to public.

## Fetch model collections

Fetch a list of model entries using the model's collection endpoint at `wp-json/wp/v2/[model-plural-name]`.

The response includes an array of model entries with Atlas Content Modeler field data in an `acm_fields` object.

For example, with a public “Rabbits” model that has a plural name of “rabbits”:

```sh
curl https://example.com/wp-json/wp/v2/rabbits

[
  {
    "id": 57719,
    "date": "2021-08-09T15:29:42",
    "date_gmt": "2021-08-09T15:29:42",
    "guid": {
      "rendered": "https://example.com/?post_type=rabbit&#038;p=57719"
    },
    "modified": "2021-08-09T15:29:42",
    "modified_gmt": "2021-08-09T15:29:42",
    "slug": "57719",
    "status": "publish",
    "type": "rabbit",
    "link": "https://example.com/rabbit/57719/",
    "template": "",
    "acm_fields": {
      "name": "Thumper"
    },
    "_links": {
      …
    }
  },
  {
    "id": 57716,
    "date": "2021-08-09T15:28:35",
    "date_gmt": "2021-08-09T15:28:35",
    "guid": {
      "rendered": "https://example.com/?post_type=rabbit&#038;p=57716"
    },
    "modified": "2021-08-09T15:28:35",
    "modified_gmt": "2021-08-09T15:28:35",
    "slug": "57716",
    "status": "publish",
    "type": "rabbit",
    "link": "https://example.com/rabbit/57716/",
    "template": "",
    "acm_fields": {
      "name": "Roger"
    },
    "_links": {
      …
    }
  }
]
```

## Fetch a single model entry

Request a single model entry at `wp-json/wp/v2/[model-plural-name]/[entry-id]`.

```sh
curl https://example.com/wp-json/wp/v2/rabbits/57719

{
  "id": 57719,
  "date": "2021-08-09T15:29:42",
  "date_gmt": "2021-08-09T15:29:42",
  "guid": {
    "rendered": "https://example.com/?post_type=rabbit&#038;p=57719"
  },
  "modified": "2021-08-09T15:29:42",
  "modified_gmt": "2021-08-09T15:29:42",
  "slug": "57719",
  "status": "publish",
  "type": "rabbit",
  "link": "https://example.com/rabbit/57719/",
  "template": "",
  "acm_fields": {
    "name": "Thumper"
  },
  "_links": {
    …
  }
}
```

### Relationship fields

Relationship fields display the ID of the related entry by default.

A request for `wp-json/wp/v2/[model-plural-name]/[entry-id]` for a model with a one-to-many relationship field titled “friends” might give:

```json
{
  "acm_fields": {
    "name": "Thumper",
    "friends": [ 123, 124 ]
  },
}
```

In keeping with [WordPress Core REST conventions](https://developer.wordpress.org/rest-api/using-the-rest-api/linking-and-embedding/), responses for entries containing relationship fields also include a `_links` object with related resources and their URIs:

```json
{
  "_links": {
    "related": [
      {
        "href": "https://example.com/wp-json/wp/v2/deer/123",
        "embeddable": true
      },
      {
        "href": "https://example.com/wp-json/wp/v2/rabbits/124",
        "embeddable": true
      }
    ],
  },
}
```

Send additional requests to fetch data about related resources.

Alternatively, fetch data about related entries in your first request by passing the `_embed` query string in the request URI:

`wp-json/wp/v2/[model-plural-name]/[entry-id]?_embed`

You will see embeddable resources from `_links` expanded in an `_embedded` object at the top level of the response, without having to make additional requests:

```json
{
  "_embedded": {
    "related": {
      "123": {
        "id": 123,
        "title": "Bambi",
        "acm_fields": {
          "field": "field value"
        }
      },
      "124": {
        "id": 124,
        "title": "Blossom",
        "acm_fields": {
          "field": "field value"
        }
      }
    }
  }
}
```
