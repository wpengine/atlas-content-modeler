#  End of Life info for Atlas Content Modeler

Atlas Content Modeler is entering an end-of-life phase. During this phase, we will continue to support Atlas Content Modeler to ensure it is secure and functional, giving you time to move your site to our recommended replacement. While security and critical bug fixes will continue to be provided through 2024, no new feature development will happen in Atlas Content Modeler. ACM will be shutdown in early 2025.

## Recommended replacement
We recommend you move your site to Advanced Custom Fields (ACF) before ACM's final shutdown date. ACF supports the Custom Post Types, Field Groups, and Custom Taxonomies necessary to replace ACM's functionality.

## Moving from ACM to ACF
Moving your site from ACM to ACF requires a few steps, and it will require changes on the WordPress side and in any software consuming ACM data via WPGraphQL or the REST API.

There are numerous ways you can programmatically access your data in ACF. ACF has its own set of PHP APIs for CRUD operations, it integrates with the WordPress REST API, and there's a plugin that offers a GraphQL integration.

You need to install, activate, and configure the following plugins:
- [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/)
- [WPGraphQL](https://wordpress.org/plugins/wp-graphql/) (Optional)
- [WPGraphQL for ACF](https://wordpress.org/plugins/wpgraphql-acf/) (Required for GraphQL integration)
- [WP Engine Atlas Headless Extension](https://wp-product-info.wpesvc.net/v1/plugins/wpe-atlas-headless-extension?download) (Optional. Required for importing/exporting Atlas Blueprints.)

## Setting up your data models in ACF
To set up your data models in ACF, you need to create the Custom Post Types and Field Groups that correspond with your ACM data models, and you must associate the Field Groups with the Custom Post Types.

Here is the documentation for:
- [Creating Custom Post Types in ACF](https://www.advancedcustomfields.com/resources/registering-a-custom-post-type/)
- [Creating Field Groups in ACF](https://www.advancedcustomfields.com/resources/creating-a-field-group/)
- [Creating Custom Taxonomies in ACF](https://www.advancedcustomfields.com/resources/registering-a-custom-taxonomy/)

## Updating your GraphQL queries
Since the schema structure is different between ACM and ACF, after you have moved your data models from ACM to ACF, you must update the code that communicates with the WPGraphQL API.

Example query using ACM:
```
query ACMProjects {
  projects(first: 10) {
    nodes {
      ...ProjectFields
    }
  }
}

fragment ProjectFields on Project {
  projectTitle
  summary
  contentArea
}
```

Same example query using ACF. Note the only difference in this example is the `ProjectFields` fragment. Your code may be different and require additional changes.
```
query Projects {
  projects(first: 10) {
    nodes {
      ...ProjectFields
    }
  }
}

fragment ProjectFields on Project {
  projectFields {
    projectTitle
    summary
    contentArea
  }
}
```

See the official [documentation for WPGraphQL for ACF](https://acf.wpgraphql.com).



## Deactivate and uninstall ACM
After you have confirmed your data models are transferred to ACF, you can deactivate ACM and uninstall it.
