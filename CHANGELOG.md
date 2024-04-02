# Atlas Content Modeler Changelog
## 0.26.2 - 2024-04-02
### Chores
- Update project dependencies for security #688.
- Tested the project for WordPress 6.5 compatibility.

## 0.26.1 - 2024-01-23
### Fixed
- Fixed issue where having empty repeater fields would show a list of entries when the browser tab loses and regains focus. GitHub issue #627.
### Added
- Added changelog for 0.26.0, which was accidentally omitted from the previous release. Please read the changes for 0.26.0 to learn more about the deprecation of ACM.

## 0.26.0 - 2024-01-16
### Added
- Added an admin notice about the deprecation of Atlas Content Modeler. ACM will be maintained for security and compatibility through 2024. Read more about the plan and recommended replacement here: https://github.com/wpengine/atlas-content-modeler/blob/main/docs/end-of-life/index.md

## 0.25.0 - 2023-11-08
### Fixed
- GraphQL queries now work properly when repeating Rich Text fields are optional and have no values.
- GraphQL queries now work properly when Relationship fields are optional and have no connections.

## 0.24.0 - 2023-02-23
### Fixed
- Querying posts with optional relationships will no longer raise an error in WPGraphQL when connections do not exist. Addresses changes made in WPGraphQL 1.13.x.

## 0.23.0 - 2022-11-15
### Added
- Added a “Post Type Archives” option on the edit model screen to control custom post type archives (The WordPress `has_archive` setting. false by default).

## 0.22.0 - 2022-09-22
### Added
- New `wp acm model change-id` WP-CLI command to change a model's ID and migrate existing posts to the new ID.

### Changed
- Models can no longer be registered with IDs that match special WordPress Core names, such as ‘type’ and ‘theme’.
- Existing models using reserved model IDs are disabled to prevent fatal errors and unexpected behavior from WordPress Core.
- Disabled models with reserved model IDs display a message on the model index page, with a link to docs explaining how to change the model ID: https://github.com/wpengine/atlas-content-modeler/blob/main/docs/help/model-id-conflicts.md.

## 0.21.1 - 2022-09-07
### Fixed
- No functional changes. Bumped version due to deploy issue.

## 0.21.0 - 2022-09-07
### Added
- Added `validate_integer()` and `validate_decimal()` functions.

### Fixed
- Decimal values ending in 0, such as 1.0, will now pass validation for validate_number_type() for integers.

## 0.20.0 - 2022-08-09
### Added
- The email field gains a “make this field unique” setting so that emails can not be used more than once.
- `wp acm reset` WP-CLI command to remove ACM models, taxonomies, posts, and media.

## 0.19.2 - 2022-07-25
### Fixed
- Prevent a fatal error during the blueprint import cleanup process.

## 0.19.1 - 2022-07-13
### Added
- Related entries in relationship fields now have an “Edit” link to view or edit them in a new tab.
- Added model field API Identifier to model field list.

### Fixed
- Fields will now save empty values when existing content is removed.
- 0 can now be entered into number fields via the `insert_model_entry()` PHP function.
- Decimals can now be entered into number fields via the `insert_model_entry()` PHP function.
- You can now edit the case of model singular and plural names.

## 0.19.0 - 2022-06-29
### Added
- The `wp acm blueprint import` WP-CLI command can now take a path to a local directory containing an `acm.json` blueprint manifest.
- Developers working on the ACM plugin can now use the `wp acm blueprint import demo` WP-CLI command to import demo models with different field configurations.
- Added field indexes to repeatable fields.

### Fixed
- Number fields with '0' values are now saved correctly.

## 0.18.0 - 2022-06-16
### Changed
- Text fields can now use “title” as their API identifier if “use this field as the entry title” is ticked.
- Field validation now returns translatable errors.
- Crud function insert_model_entry() will now append relationship ids.
- Text field entries via CRUD are now validated for min and max characters.
- Text field entries created via the PHP API are now validated for minRepeatable and maxRepeatable.
- Extended timeout for `wp acm blueprint import` command from 5 seconds to 15 seconds.
- Number field entries via CRUD are now validated for min, max, and step values as well as type.

### Fixed
- Issue where adding a new repeating field to an existing model schema could break GraphQL queries under certain conditions.
- Empty field values are no longer saved to the database.
- Relationship fields no longer resolve as “null” in GraphQL results for models with different singular names and API identifiers.

### Added
- Added email validation when using insert_model_entry() crud function.
- Added ability to add newly created models from the top admin menu dropdown right after creation without a refresh of the page.
- Added validation for repeatable text fields for insert_model_entry() crud function.
- Added validation for repeatable date fields for insert_model_entry() crud function.
- Added validation for repeatable number fields for insert_model_entry() crud function.
- Added validation for repeatable email fields for insert_model_entry() crud function.
- Added documentation for crud functions.

## 0.17.0 - 2022-05-05
### Added
- Mutations support to create, update and delete entries via GraphQL. All fields are supported except media and relationships for now. Find examples at https://github.com/wpengine/atlas-content-modeler/blob/main/docs/mutations/index.md.
- Function insert_entry_model() for inserting a model entry and fields.
- Function update_model_entry() for updating an existing model entry and fields.
- Function replace_relationship() to replace/add model entry relationships.
- Function add_relationship() to append model entry relationships.
- Function get_relationship() to retrieve the relationship object.
- Function get_model() to retrieve the model schema.
- Function get_field() to retrieve the model field schema.
- Email field that includes the following: repeatable, entry title, required, and advanced settings to constrain emails to domain.
- New "Add New Entry" option in content model dropdowns.
- Added the ability to upload a ZIP file from your Local File System for BluePrints.

### Changed
- Pressing enter in a repeating number, date or single line text field now moves focus to the next field, or adds a new field if focus is in the last field.
- Boolean fields in REST responses will now return `true` or `false` instead of `"on"` or `[empty string]`.
- Focus now moves to the new input field when adding a new repeating number, date or single line text field row.

### Fixed
- Title values are now saved to the wp_posts table as expected, instead of being exposed with WP filters. This fixes a few things, such as queries that search the post title field.
- Post slugs are now generated from the post title value, like they are for post types built into WordPress.
- Taxonomy terms that already exist are skipped when importing a blueprint.

## 0.16.0 - 2022-04-06
### Added
- The date field now has the “make this field repeatable” option to let publishers add multiple dates within each date field.

### Changed
- The first field now gains focus when creating a new ACM post.
- Telemetry is no longer sent for staging sites (those with  *.wpengine.com domains). Telemetry for other domains still requires opt-in, and is off by default.

## 0.15.0 - 2022-03-24
### Added
- Rich Text, Number and Media fields have a new “make repeatable” option. Enable it on new fields to let publishers add multiple rich text, number or image entries in one field.
- Repeatable text fields now include a “minimum” and “maximum” repeatable limit in advanced settings.
- Added a “Use Permalink Base” option on the edit model screen to set the WordPress `with_front` setting (true by default). Untick this to tell WordPress not to prefix your model entry URLs with custom prefixes from your WordPress permalink settings. For example, a site with a permalink structure of `/posts/%postname%/` will have post URLs of `/posts/your-acm-model-name/your-post/` by default. Edit your model and untick “Use Permalink Base” if you prefer a URL structure of `/your-acm-model-name/your-post/`.
- `wp acm blueprint export` now accepts a `--wp-options` flag to export a comma-separated list of WordPress options from the `wp_options` table. No options are exported by default. Example: `wp acm blueprint export --wp-options='blogname, permalink_structure`
- `wp acm blueprint import` now updates WordPress options if blueprints contain a `wp-options` key with a list of options values, keyed by option name.

### Changed
- The `wp acm blueprint export` and `wp acm blueprint import` commands now include all taxonomies for collected post types, including category and post_tag taxonomy terms for the WordPress core 'post' type.

### Fixed
- The delete model prompt no longer shows “undefined” in its title during model deletion.
- Improved checkbox styling in field settings.
- Options buttons now use the mouse pointer cursor.
- The `wp acm blueprint import` command no longer reports “Could not read an acm.json file” if the blueprint zip file was renamed after creation.
- Fixed issue where it was possible to improperly lead a model ID with a number.
- Improved validation messages for integer and decimal values in the number field.
- WordPress admin notices no longer overlay the Screen Options button on ACM entry pages.

## 0.14.0 - 2022-02-10
### Added
- New `wp acm blueprint import` WP-CLI command to import ACM data programmatically. This prepares for future work to restore ACM blueprints. See `wp help acm blueprint import` for options.
- New `wp acm blueprint export` WP-CLI command to export ACM data into a blueprint zip file. See `wp help acm blueprint export` for options.

### Fixed
- Featured Image Fields - Previously, featured images allowed for videos and other media to display. Now the modal limits to images in line with how Core featured images work.
- Fixed PHP notice that happens under certain conditions when a featured image is not provided.

## 0.13.0 - 2022-02-01
### Added
- Text Repeater Field - Added repeatable property to single and multi line text fields for returning arrays of publisher defined strings.
- Opt-in anonymous usage tracking to help us make Atlas Content Modeler better (disabled by default).

### Changed
- The title field of a model can no longer be changed once set, unless you delete the original title field. This prepares upcoming work to save title field data to WordPress post titles, allowing title field content to be searchable.

### Fixed
- Ensure Rich Text fields load for publishers even if WordPress Core editor scripts are slow to execute.

## 0.12.1 - 2022-01-12
### Fixed
- Prevent PHP fatal errors on sites running < PHP 7.4 under certain conditions when no models exist.
- Prevent model and taxonomy creation and updates if singular or plural labels conflict with existing WPGraphQL fields.

## 0.12.0 - 2021-12-15
### Added
- Debug info is now added to GraphQL responses when GraphQL Debug Mode is enabled and a request for Private models is made as an unauthenticated user.

### Fixed
- Improved display of admin notices on pages in the Content Modeler admin menu.
- Saving a model that has no changes no longer causes an error to be displayed.

## 0.11.0 - 2021-12-01
### Added
- Set any Media Field as the [Featured Image](https://www.wpgraphql.com/docs/media/#query-a-post-with-its-featured-image) for its model.
- Create checkbox and radio button lists with the new Multiple Choice Field (beta).

### Fixed
- Post titles are now available in WPGraphQL responses.
- Prevent reserved taxonomy slugs from being used as taxonomy slug.
- Used consistent labels to describe the taxonomy ID.
- Changing model plural name now updates the sidebar menu item automatically.
- Issue where sidebar menu doesn't expand under certain conditions.

### Changed
- Standardized WP_Error codes for internal REST endpoints. All error statuses now use an acm_ prefix instead of a mix of wpe_ and atlas_content_modeler_.
- Removed dead code.

## 0.10.0 - 2021-11-18
### Added
- Relationship fields with reverse references enabled are now editable from the reverse side. Tick “Configure Reverse Reference” when creating your relationship field to use reverse references.
- Post slugs are now editable in ACM entries. Enable them via Screen Options when editing a post.

### Fixed
- Integer number fields can no longer use decimal values for the step, minimum and maximum settings.

## 0.9.0 - 2021-11-03
### Added
- Fields now have an optional description which can be used to inform publishers what a field is for. The description is displayed above the field on the post entry screen.
- Relationship field now supports one-to-one and one-to-many connections.

### Fixed
- ACM now prevents field slugs from conflicting with reserved slugs already used by WPGraphQL.
- One-to-one field cardinality is now properly enforced.
- API Identifier character limit is now properly enforced.
- API Identifier validation now properly accounts for dashes and underscores.
- Reverse API Identifier conflict detection no longer causes conflicts with the field being edited.
- Added `publicly_queryable` parameter when registering custom post types. This fixes an issue caused by changes in [WPGraphQL #2134](https://github.com/wp-graphql/wp-graphql/issues/2134).

### Changed
- Posts without a title field no longer have their title saved as "Auto Draft". The fallback title is now entry[post id goes here].

## 0.8.0 - 2021-10-13

### Added
- Share models between sites using the new Export and Import models feature. Visit Content Modeler → Tools to get started.
- Added "author" support to custom post types. This unlocks new functionality such as assigning specific users to a post, and querying posts by author in WPGraphQL.

### Fixed
- Fixed bug where the model icon could not be changed when editing an existing model.
- Fixed bug in the Number field where you could not define a Step value without also defining a Max value.
- Improved duplicate field slug detection to ensure forward and reverse slugs for the Relationship field are unique in WPGraphQL.

### Changed
- The `@wordpress/i18n` package is no longer bundled in the plugin's scripts, relying on the version that ships with WordPress instead.

## 0.7.0 - 2021-10-04

### Added
- Relationship Field: one-to-one and one-to-many relationships were renamed to many-to-one and many-to-many to accurately reflect their function.
- Relationship Field: fields can now optionally include reverse references.
- Relationship Field: added [Beta] flag as the feature takes shape.
- Chore: set "Requires at least" to WordPress version 5.7
- Chore: set "Requires PHP" to version 7.2

### Fixed
- Fixed bug where the app prompted about "Unsaved changes" when no changes had been made.

## 0.6.0 - 2021-09-09

### Added
- Create one-to-one and one-to-many relationships between model entries with the new Relationship field.
- A plugin icon will appear on the plugin update page during future updates.

### Fixed
- Improved modal scroll behavior and positioning.

## 0.5.0 - 2021-08-12

### Added
- You can now add custom Taxonomies and assign them to your models. Visit Atlas Content Modeler → Taxonomies to get started.
- Models and Taxonomies submenu items now appear in the admin sidebar below Atlas Content Modeler.

### Changed
- Refactored PHP tests.

## 0.4.2 - 2021-08-04

### Added
- Ability to choose an icon when creating or editing a model.
- Option to restrict file types for the media field.
- Generate WordPress changelog from the Markdown changelog so that changes are visible from the WordPress changes modal.
- Plugin developer improvements: GitHub Pull Request template; Code Climate configuration; Makefile for test environments.

### Changed
- Change “API Identifier” field title on model entry forms to “Model ID” with a new description to better reflect its use.
- Continuous Integration: the generated plugin zip is now tested and verified before deploying.

### Fixed
- Improve query generation for “Open in GraphiQL” to include lowercase model names and models with the same plural and singular name.
- Improve sanitization of model slugs. Includes safe migration of existing model slugs.
- Prevent a PHP warning during title filtering if post info can not be found.
- Improve number field validation.

## 0.4.1 - 2021-06-24
### Added
- Generate POT language file for translations.

### Changed
- Use `include` in place of `require` so that missing or corrupt files do not take WordPress down.

### Removed
- Removed the Multiple Choice field for now while we add support for custom choice API IDs.

## 0.4.0 - 2021-06-22
- First public release. There may be breaking changes until 1.0.0.

### Added
- New Multiple Choice field (beta) to create radio and checklist groups.
- New “Send Feedback” button to share your experience with us.
- New model option for “Private” or “Public” API visibility.
- The Text field now includes an optional character minimum and maximum count in Advanced Settings.
- The Number field now includes an optional minimum, maximum and step value in Advanced Settings.
- Prompt to complete edits to an open field when attempting to open another field.
- Added LICENSE, CONTRIBUTING and CHANGELOG files.

### Changed
- REST responses now show Atlas Content Modeler fields under an `acm_fields` property.
- REST responses now display detailed information about media fields.
- Changed “Text Length” to “Input Type” in the Text field. Text length is now determined in Advanced Settings. Input type lets developers choose an input or textarea field.
- Increased the clickable area on dropdown menu items in the developer app.
- Adjusted styling in the publisher app.
- Refactored default value handling to improve field load times.
- Internal REST routes now include an `atlas` prefix.
- The README now includes a getting started guide.

### Fixed
- Pressing return in the publisher app will now submit the form, instead of clearing fields.
- Corrected an issue preventing fields from appearing in REST responses.
- Prevented custom fields from showing in REST when `show_in_rest` is false.
- Strings in the publisher and developer interfaces are now translatable.
- Fixed Jest tests and add to Continuous Integration workflow.
- Improved admin URL handling for WordPress sites hosted in a subfolder.
- Improved plugin update error messages to include the name of the plugin reporting them.
- Prevented a styling issue with the Content Modeler admin menu item.

## 0.3.0 - 2021-06-01
### Added
- Developers can now mark fields as required.
- Publishers will see inline errors prompting them to fill required fields.

### Changed
- Rebranded to Atlas Content Modeler.
- Changed data storage format.
- Changed model data option name from wpe_content_model_post_types to atlas_content_modeler_post_types.
- Updated admin sidebar icons for settings and entries.
- Improved Rich Text fields, including adding media support.
- Open fields now close when another field is created or opened.
- Media fields now have a WPGraphQL type of MediaItem instead of String to enable complete queries for media information.

### Fixed
- Improved model and field list appearance at mobile screen widths.
- Improved client-side field validation for publishers.
- Hid duplicate page title on new entry screens.
- Corrected headers when editing entries.
- Protected meta fields to prevent them appearing in the Custom Fields meta box.

### Removed
- Repeater fields have been removed.
- Screen options have been removed from entry pages for content model post types.
- Post thumbnail support has been removed for content model post types.

## 0.2.0 - 2021-05-07
### Added
- Publishers can now enter model entries via a form.
- Developers have the option to set a field as the title field.
- The plugin now checks for updates.
- Model and field options now close on blur or when the escape key is pressed.
- Model options include “Open in GraphiQL” if the WPGraphQL plugin is active.
- Improved developer tooling for linting, including pre-commit hooks.

### Changed
- The media field is restricted to single file uploads for now.

### Removed
- Unused “Created on” column from the model table.

## 0.1.0
- Initial version.
