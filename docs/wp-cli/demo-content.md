# Atlas Content Modeler Demo Content

Atlas Content Modeler includes demo models and fields that developers can use to explore and test the plugin.

## Importing demo content

Run this WP-CLI command in your WordPress development environment to import demo content:

```
wp acm blueprint import demo
```

See the [ACM WP-CLI docs](https://github.com/wpengine/atlas-content-modeler/blob/main/docs/wp-cli/index.md) to learn how to run WP-CLI commands.

## Updating demo content

Run `wp acm blueprint import demo` on a clean WordPress site, make your changes in WordPress and then run:

```
wp acm blueprint export --post-types=demotext,demorich,demonumber,demodate,demomedia,demoboolean,demorelationship,demomultiplechoice,demoemail --open
```

You will need to adjust the `--post-types` value if you have created additional models.

Overwrite the `acm.json` in the `atlas-content-modeler/includes/wp-cli/demo` directory with the newly exported `acm.json` file.
