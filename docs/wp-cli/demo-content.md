# Atlas Content Modeler Demo Content

Atlas Content Modeler includes demo models and fields that developers can use to explore and test the plugin.

## Importing demo content

Run this WP-CLI command in your WordPress development environment to import demo content:

```
wp acm blueprint import demo
```

See the [ACM WP-CLI docs](https://github.com/wpengine/atlas-content-modeler/blob/main/docs/wp-cli/index.md) to learn how to run WP-CLI commands.

## Updating demo content

Developers working on ACM who want to update the demo blueprint stored locally within the plugin at `atlas-content-modeler/includes/wp-cli/demo` can follow these steps:

1. Run `wp acm blueprint import demo` on a clean WordPress site.

2. Make your changes in WordPress and then run:

    ```
    wp acm blueprint export --post-types=demotext,demorich,demonumber,demodate,demomedia,demoboolean,demorelationship,demomultiplechoice,demoemail --open
    ```

You will need to adjust the `--post-types` value if you have created additional models.

3. Overwrite the `acm.json` in the `atlas-content-modeler/includes/wp-cli/demo` directory with the newly exported `acm.json` file.
