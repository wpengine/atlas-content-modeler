# Atlas Content Modeler GraphQL Mutations

Atlas Content Modeler supports GraphQL [mutations](https://graphql.org/learn/queries/#mutations) (data writes) when the [WPGraphQL plugin](https://wordpress.org/plugins/wp-graphql/) is active.

Developers can create, update and delete ACM model entries (“posts”) via GraphQL requests with these limitations:

-   GraphQL mutations must be [authenticated](https://www.wpgraphql.com/docs/authentication-and-authorization/) as a user with the `create_posts`, `edit_posts` or `delete_post` [capabilities](https://wordpress.org/support/article/roles-and-capabilities/).
-   The media and relationship fields do not yet support mutations.

## Example model

We'll use a model named 'rabbits' with these fields for our sample mutations:

| ID | Field Type |
|-|-|
| name | Text [title field, required] |
| description | Rich Text |
| birthday | Date |
| colors | Multiple choice [multi-select] |

## Create an ACM entry using a GraphQL mutation

Create a rabbit with this authenticated query:

```
mutation CreateRabbit {
  createRabbit(
    input: {
      status: PUBLISH
      name: "Benny"
      description: "<p>Benny is the littlest bunny.</p>"
      birthday: "2022-01-01"
      colors: ["black", "white", "brown"]
    }
  ) {
    rabbit {
      id
	  databaseId
	  date
    }
  }
}
```

- The `CreateRabbit` [operation name](https://graphql.org/learn/queries/#operation-name) after the `mutation` keyword is an optional reference to make our code more readable. It also helps the GraphQL server to show us where errors occurred.
- `createRabbit` is the mutation we want to run. WPGraphQL registers `create[SingularModelName]`, `update[SingularModelName]` and `delete[SingularModelName]` mutations for all ACM models.
- The `input: { … }` block is where you provide post and field data to write. Available inputs include ACM fields and those that WPGraphQL registers for the [post object create mutation](https://github.com/wp-graphql/wp-graphql/blob/cc0b383259383898c3a1bebe65adf1140290b37e/src/Mutation/PostObjectCreate.php#L44). Mutation will fail with “Field `{field-id}` of type `{type}` was not provided” if you forget to include ACM required fields in the input block.
- The final `rabbit { … }` block lets us return properties of the new rabbit entry we are interested in when the mutation is successful. The `id` is the unique global ID that GraphQL assigns to the new entry: a hash of the object type and its WordPress ID, such as `"cG9zdDoyMDcw"`. Use it to update and delete the entry via subsequent GraphQL mutations. The `databaseId` is the ID that WordPress assigns to the post, such as `123`.

## Update an ACM entry using a GraphQL mutation

Update a rabbit entry by passing its [WPGraphQL global ID](https://www.wpgraphql.com/docs/wpgraphql-concepts/#node-global-id) in the `id` input.

Get the entry's global ID from its WordPress ID with this GraphQL query:

```
{
  rabbit( id: 123, idType: DATABASE_ID ) {
    id
  }
}
```

Or from the entry's slug with this GraphQL query:

```
{
  rabbit( id: "hello-world", idType: SLUG ) {
    id
  }
}
```

If using the WPGraphQL PHP API to prepare and send GraphQL requests, fetch an object's global ID with the Relay API:

```
<?php
$post_id = 123;
$global_id = \GraphQLRelay\Relay::toGlobalId( 'post', $post_id );
```

Once you have the entry's global ID, send it with your authenticated update mutation:

```
mutation UpdateRabbit {
  updateRabbit(
    input: {
      id:"cG9zdDoyMTEx"
      description: "<p>Benny is the biggest bunny now.</p>"
    }
  ) {
    rabbit {
      id
	  databaseId
	  description
    }
  }
}
```

- The `UpdateRabbit` [operation name](https://graphql.org/learn/queries/#operation-name) after the `mutation` keyword is optional but makes our code and error messages more readable.
- `updateRabbit` is the mutation we want to run.
- The `input: { … }` block requires the `id` parameter and any field data we want to update. Update mutations can omit required ACM fields.
- The final `rabbit { … }` block lets us return properties of the new rabbit entry we are interested in when the mutation is successful.

Queries (reads) and mutations (writes) can not be combined in the same request. You can not query a post's global ID and pass it to an update mutation in the same request, for example.

## Delete an ACM entry using a GraphQL mutation

Delete an entry by passing its global ID to your delete mutation.

See “Update an ACM entry” above for ways to fetch the entry's global ID.

```
mutation DeleteRabbit {
  deleteRabbit(input: {id: "cG9zdDoyMDcw"}) {
    deletedId
  }
}
```

- The `DeleteRabbit` [operation name](https://graphql.org/learn/queries/#operation-name) after the `mutation` keyword is optional but makes our code and error messages more readable.
- `deleteRabbit` is the mutation we want to run.
- `deletedId` provides us with the ID of the deleted entry on success. It should match the ID we passed in the input.

## Create, update or delete multiple entries

Use multiple mutations prefixed by an [alias](https://graphql.org/learn/queries/#aliases) in the same request to run the same mutation on more than one entry.

Here, the `RABBIT1` and `RABBIT2` aliases make each `createRabbit` mutation unique and identifiable in the response:

```
mutation CreateRabbits {
  RABBIT1: createRabbit(
    input: {
      status: PUBLISH
      name: "Benny"
      description: "<p>The littlest bunny.</p>"
      birthday: "2022-01-01"
      colors: ["black", "white", "brown"]
    }
  ) {
    rabbit {
      id
      databaseId
      title
    }
  }

  RABBIT2: createRabbit(
    input: {
      status: PUBLISH
      name: "Roger"
      description: "<p>Jumpin' jeepers.</p>"
      birthday: "1988-06-22"
      colors: ["white"]
    }
  ) {
    rabbit {
      id
      databaseId
      title
    }
  }
}
```

The `RABBIT1` and `RABBIT2` aliases appear in the response with the subfields we asked for:

```
{
  "data": {
    "RABBIT1": {
      "rabbit": {
        "id": "cG9zdDoyMTEz",
        "databaseId": 2113,
        "title": "Benny"
      }
    },
    "RABBIT2": {
      "rabbit": {
        "id": "cG9zdDoyMTE0",
        "databaseId": 2114,
        "title": "Roger"
      }
    }
  },
  "extensions": {
    "debug": []
  }
}
```

You can combine create, update and delete mutations as long as multiple mutations of the same type are aliased:


```
mutation ChangeMultipleRabbits {
  NEW_RABBIT1: createRabbit(input: {status: PUBLISH, name: "Benny"}) {
    rabbit {
      id
      databaseId
    }
  }
  NEW_RABBIT2: createRabbit(input: {status: PUBLISH, name: "Roger"}) {
    rabbit {
      id
      databaseId
    }
  }
  DELETED_RABBIT1: deleteRabbit(input: {id: "cG9zdDoyMTEz"}) {
    deletedId
  }
  DELETED_RABBIT2: deleteRabbit(input: {id: "cG9zdDoyMTE0"}) {
    deletedId
  }
  updateRabbit(input: {id: "cG9zdDoyMTE3", name: "Robin"}) {
    rabbit {
      title
    }
  }
}
```

Above, delete and create mutations have aliases because there are more than one of each. There is only one `updateRabbit` mutation so it does not need an alias.

Response data uses the alias if available, or the mutation name if there is only one mutation of that type with no alias:

```
{
  "data": {
    "NEW_RABBIT1": {
      "rabbit": {
        "id": "cG9zdDoyMTE5",
        "databaseId": 2119
      }
    },
    "NEW_RABBIT2": {
      "rabbit": {
        "id": "cG9zdDoyMTIw",
        "databaseId": 2120
      }
    },
    "DELETED_RABBIT1": {
      "deletedId": "cG9zdDoyMTEz"
    },
    "DELETED_RABBIT2": {
      "deletedId": "cG9zdDoyMTE0"
    },
    "updateRabbit": {
      "rabbit": {
        "title": "Robin"
      }
    }
  },
  "extensions": {
    "debug": []
  }
}
```

## Troubleshooting

### Cannot query field "mutation" on type "RootQuery"

Mutations must appear at the root level, without being wrapped in the curly braces common in other GraphQL queries.


❌ **Incorrect**

```
{
  mutation DeleteRabbit {
    deleteRabbit(input: {id: "cG9zdDoyMDcw"}) {
      deletedId
    }
  }
}
```

✅ **Correct**

```
mutation DeleteRabbit {
  deleteRabbit(input: {id: "cG9zdDoyMDcw"}) {
    deletedId
  }
}
```

### Field `{field-id}` of type `{type}` was not provided

If an ACM field is required, you must provide the field name and value as input for create mutations.

### Field `{field-id}` argument \"input\" requires type `{type}`, found `{value}`.

GraphQL is strongly-typed. The values you provide must be of the type registered for that field in the GraphQL type system.

Sending a numeric value of `123` for an ACM text field will generate a type mismatch because text fields have the `String` type. Adjust your input to match the expected type: a string of `"123"` will be accepted in this case.

### Sorry, you are not allowed to create|update|delete `{model-id}`

The request was not [authenticated](https://www.wpgraphql.com/docs/authentication-and-authorization/) or was authenticated by a WordPress user without the `create_posts`, `edit_posts` or `delete_post` [capabilities](https://wordpress.org/support/article/roles-and-capabilities/). Note that authors must have the `edit_others_posts` capability to mutate an entry that they did not create.

If you see the “not allowed” error on the _WPGraphQL → GraphiQL IDE_ admin page, click the “switch to execute as the logged-in user” icon next to the Execute Query button to authenticate subsequent requests. A green light on the user icon means that requests are authenticated.

You may see, “Sorry, you are not allowed to delete `{model-id}`” if you tried to delete a model entry that no longer exists.

### No `{model-id}` could be found to update

The ID of the model you tried to update could not be found. Check that you are sending the [global ID](https://www.wpgraphql.com/docs/wpgraphql-concepts/#node-global-id) instead of the WordPress ID. See “Update an ACM entry” above to learn how to fetch the global ID.

If you are already using a global ID, it may be invalid or not linked to an existing entry.

## Where to get help

- [Create issues or discussions](https://github.com/wpengine/atlas-content-modeler/issues/new/choose) in ACM's GitHub repository.
- [Join the Atlas Discord server](https://discord.gg/J2khkF9XYK) and post in the `#atlas-content-modeler` channel.
