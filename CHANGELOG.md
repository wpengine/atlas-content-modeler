# Atlas Content Modeler Changelog

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
