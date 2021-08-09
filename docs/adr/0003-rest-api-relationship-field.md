3: Allow access to relationship field information via the REST API
==================================================================

Date: 2021-08-09

Context
-------

- Relationship fields store relationships between a model entry and one or more other model entries.
- This plugin already exposes entry metadata (stored field values) in REST responses via the top-level `acm_fields` object.
- Relationship field metadata will appear in the `acm_fields` object as a reference (such as the ID) by default.
- We wish to offer the option to retrieve detailed information about related entries (such as the related post title and not just its ID) without additional REST requests.

Decision
--------

By default, REST requests for an entry with a relationship field will return the related entry ID in `acm_fields`, as well as a link to the related resource in the `_links` top-level object under a `related` key:

```sh
curl https://example.com/wp-json/wp/v2/rabbits/123 | jq

{
  "id": 123,
  …
  "acm_fields": {
    "owner": 456
  },
  "_links": {
    "related": [
      {
        "href": "https://example.com/wp-json/wp/v2/owners/456",
        "embeddable": true
      }
    ],
    "self": [
      {
        "href": "https://example.com/wp-json/wp/v2/rabbits/123"
      }
    ],
    …
  }
}
```

REST requests for the same resource with an added `_embed` query parameter will return all embeddable `_links` expanded in an `_embedded` top-level object.

```sh
curl https://example.com/wp-json/wp/v2/rabbits/123?_embed | jq

{
  "id": 123,
  …
  "acm_fields": {
    "owner": 456
  },
  "_links": {
    "related": [
      {
        "href": "https://example.com/wp-json/wp/v2/owners/456",
        "embeddable": true
      }
    ],
    "self": [
      {
        "href": "https://example.com/wp-json/wp/v2/rabbits/123"
      }
    ],
    …
  },
  "_embedded": {
    "related": {
      "456": {
        "id": 456,
        "title": "Isabella",
        "acm_fields": {
          "pets": [ 123 ],
		  "field2": "field 2 value"
        }
      }
    …
    }
  }
  …
}
```

Embedded entries with relationship meta should not add those entries to the `_links` or `_embedded` objects. This is to keep top-level `_links` and `_embedded` values scoped to the current resource and to avoid cyclical references and performance issues from deep-nested relationships.

Consequences
------------

- This decision aligns with the existing WordPress core REST mechanism for [linked and embedded items](https://developer.wordpress.org/rest-api/using-the-rest-api/linking-and-embedding/).
- Clients that need information about deep-nested structures may still need to infer resource URLs from the context and make more than one request.

Alternatives
------------

These alternatives were evaluated and rejected:

- Displaying only the ID of the related entry with no provision for returning more information in the same request. This is the easiest path but it makes it frustrating to work with related entries using the REST API.
- Returning the related entry URI instead of the entry ID as the related field value. This eases further lookups but still requires additional requests.
- Adopting an “expandable object” API such as [the REST API used by Stripe](https://stripe.com/docs/api/expanding_objects). Extending the example above, passing `expand[]="acm_fields.owner"` would return an object of expanded owner data inline and in place of the related owner ID, instead of in a separate `_embedded` object. This is an elegant idea that we may consider adopting later. We rejected it for now because:
    - It goes against the model currently used by WordPress. If users are already passing `_embed`, they do not have to learn anything new. If users are not already passing `_embed`, they can add it and interact with our API the same way as the WordPress core API. By following WordPress conventions instead of inventing our own REST query API, we reduce the chance of updates to WordPress breaking REST request handling.
    - Developers who prefer this API may be better served by using GraphQL via [WPGraphQL](https://www.wpgraphql.com/), which offers a powerful query system to request nested data already.
