# Atlas Content Modeler Blueprints

Blueprints describe ACM data to be imported by an automated process. They help to rapidly restore ACM state and WordPress post entries to accelerate user onboarding.

Blueprints can be exported and imported using [ACM’s WP-CLI commands](../wp-cli/index.md):

- `wp acm blueprint export` will dump the current state of your site to a blueprint zip file.
- `wp acm blueprint import https://example.com/path/to/blueprint.zip` will import a blueprint from a publicly-accessible URL.
- `wp acm blueprint import /filesystem/path/to/blueprint.zip` will import a blueprint from a local File Path.

## Blueprint files

An ACM blueprint is a zip file containing:

- A required `acm.json` manifest listing data and content for the blueprint import process to restore.
- An optional `media/` folder containing images linked to post entries. This includes featured images (posts with `_thumbnail_id` post meta) and files attached to ACM media fields (ACM posts with ACM media field post meta).

Find examples of `acm.json` files and a sample blueprint zip file in the [ACM test data directory on GitHub](https://github.com/wpengine/atlas-content-modeler/tree/main/tests/integration/blueprints/test-data/
).

## Blueprint contents

Blueprints can hold:

- ACM models and fields.
- ACM taxonomies.
- Terms relating to ACM taxonomies.
- Posts, including ACM entries and WordPress core post and page types by default.
- Tags applied to posts.
- Post meta relating to posts.
- Media files and data used in posts.
- ACM relationship data linking ACM entries via an ACM relationship field.

### Not yet supported

ACM blueprints do not yet export or import:

- **Post authors:** All posts are assigned to the current user on import.
- **Installed plugins:** ACM does not yet install or activate plugins.

If you'd like to see support for these please [open or add to a discussion](https://github.com/wpengine/atlas-content-modeler/discussions).


## Blueprint troubleshooting

**Blueprint export can fail with these reasons:**

| Error | Solution |
| ----- | -------- |
| The manifest has a missing or empty meta.name property | Check the blueprint's `acm.json` file for a meta.name property. |
| The manifest meta.name resulted in an empty folder name. | Check the meta.name in `acm.json` (or the name override provided via the `--name` flag via WP-CLI) contains at least one alphanumeric character. |
| Error saving temporary file | The manifest could not be written. Check disk space and file permissions. |
| Unable to create blueprint zip file. ZipArchive not available. | ACM needs the ZipArchive PHP core library to write zip files. You will need to enable this via your web host or local PHP configuration to create ACM blueprints. |
| Unable to create blueprint zip file. | Check disk space and file permissions. |
| Could not add manifest to zip file. | Check disk space and file permissions. |
| Could not add media to zip file. | Check disk space and file permissions. |
| Could not save blueprint zip file. | Check disk space and file permissions. |


**Blueprint import can fail with these reasons:**

| Error | Solution |
| ----- | -------- |
| Please provide a URL to a blueprint zip file. | Provide a URL to a publicly-accessible blueprint zip file. |
| [Any HTTP fetch error] | Check the blueprint zip URL is publicly accessible from your web browser and try again. |
| Received unexpected error downloading zip. | Check the blueprint zip URL is publicly accessible from your web browser and try again. |
| Received empty response body. | Check the blueprint zip URL is not corrupt by downloading it with your web browser. |
| Error saving temporary file | Check disk space and file permissions. |
| Error saving file | Check disk space and file permissions. |
| Provided file type is not supported. | Confirm the provided URL points to a zip file. |
| Could not read blueprint file | Confirm the blueprint file unzips. Check disk space and file permissions. |
| Could not read an acm.json file in the blueprint folder. | Confirm the blueprint zip contains a file named acm.json in the blueprint root folder when unzipped. |
| acm.json is missing the required meta.requires.acm property. | Check the acm.json manifest contains a meta.requires.acm property. |
| acm.json is missing the required meta.requires.wordpress property. | Check the acm.json manifest contains a meta.requires.wordpress property. |
| acm.json requires an ACM version of n but the current ACM version is y. | Update ACM to the requested version. |
| acm.json requires a WordPress version of x but the current WordPress version is y. | Update WordPress to the requested version. |
| A model with slug ‘x’ already exists | Remove existing ACM models with the shared slug, alter the model slug in the blueprint's acm.json file, or try another blueprint. |
| A singular name of “x” is in use. | Remove existing ACM models with the shared singular name, alter the model singular name in the blueprint's acm.json file, or try another blueprint. |
| A plural name of “x” is in use. | Remove existing ACM models with the shared plural name, alter the model plural name in the blueprint's acm.json file, or try another blueprint. |
| Models not updated. Reason unknown. | Indicates failure to write to the WordPress database. Could be caused by database corruption or unavailability. Try again, contact your web host for help, or post in the [ACM GitHub discussions page](https://github.com/wpengine/atlas-content-modeler/discussions) with a link to your blueprint file. |
| Could not read media file at x | Check the blueprint zip contains media files. Check disk space and file permissions. |


Blueprint import can also generate a warning (but still succeed):

| Warning | Meaning |
| ----- | -------- |
| “Taxonomy exists” or similar | A taxonomy from the blueprint already exists on your site. ACM will not attempt to re-import it. |
| “Term exists” or similar | A term from the blueprint already exists on your site. ACM will not attempt to re-import it. |
| “Tag exists” or similar | A tag from the blueprint is already assigned to a post. ACM will not attempt to re-assign it. |
