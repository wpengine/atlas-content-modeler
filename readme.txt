=== Atlas Content Modeler ===
Requires at least: 5.7
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 0.26.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author: WP Engine
Contributors: antpb, apmatthe, chriswiegman, jasonkonen, kevinwhoffman, markkelnar, matthewguywright, mindctrl, modernnerd, rfmeier, wpengine

A WordPress plugin to create custom post types, custom fields, and custom taxonomies for headless WordPress sites.

== Description ==
**IMPORTANT:** Atlas Content Modeler is entering an end-of-life phase. During this phase, we will continue to support Atlas Content Modeler to ensure it is secure and functional, giving you time to move your site to our recommended replacement. While security and critical bug fixes will continue to be provided through 2024, no new feature development will happen in Atlas Content Modeler. The plugin will be shutdown in early 2025.

Atlas Content Modeler (ACM) is a content modeling solution for WordPress. Using an intuitive interface, you can create custom post types, as well as custom fields and taxonomies for those post types, with ease.

### For Developers
Developers get a modern content modeling system that automatically integrates with WPGraphQL and the WordPress REST API. No need to write code or install other plugins!

### For Publishers
Publishers get friendly and familiar content entry pages.

== Installation ==
This plugin can be installed directly from your WordPress site.

1. Log in to your WordPress site and navigate to **Plugins &rarr; Add New**.
2. Type "Atlas Content Modeler" into the Search box.
3. Locate the Atlas Content Modeler plugin in the list of search results and click **Install Now**.
4. Once installed, click the Activate button.

It can also be installed manually using a zip file.

1. Download the Atlas Content Modeler plugin from WordPress.org.
2. Log in to your WordPress site and navigate to **Plugins &rarr; Add New**.
3. Click the **Upload Plugin** button.
4. Click the **Choose File** button, select the zip file you downloaded in step 1, then click the **Install Now** button.
5. Click the **Activate Plugin** button.

We recommend that you set Permalinks to a value other than “Plain” in your WordPress dashboard at Settings → Permalinks.

== Screenshots ==
1. Creating a new content model
2. Adding a Text field to a content model
3. Adding a Relationship field to a content model. This allows you to create relationships between posts.
4. One-click access to querying content models in WPGraphQL's GraphiQL interface
5. Example of querying a content model in GraphiQL
6. Creating a new custom taxonomy
7. A list of custom taxonomies

== Frequently Asked Questions ==
= Can Atlas Content Modeler be used with traditional WordPress sites? =
ACM is primarily intended for headless WordPress applications. For that reason, the WordPress REST API and WPGraphQL are the only two officially supported APIs. That said, it is possible to fetch the data for your models in a traditional WordPress site by using the `rest_do_request()` PHP function that the REST API provides or the `graphql()` PHP function that WPGraphQL provides.
= Where can I submit bug reports and feature requests? =
You can submit feature requests and open bug reports in our [GitHub repo](https://github.com/wpengine/atlas-content-modeler).

== Changelog ==

= 0.26.2 - 2024-04-02 =

* **Chores:** Update project dependencies for security #688.
* **Chores:** Tested the project for WordPress 6.5 compatibility.

= 0.26.1 - 2024-01-23 =

* **Fixed:** Fixed issue where having empty repeater fields would show a list of entries when the browser tab loses and regains focus. GitHub issue #627.
* **Added:** Added changelog for 0.26.0, which was accidentally omitted from the previous release. Please read the changes for 0.26.0 to learn more about the deprecation of ACM.

= 0.26.0 - 2024-01-16 =

* **Added:** Added an admin notice about the deprecation of Atlas Content Modeler. ACM will be maintained for security and compatibility through 2024. Read more about the plan and recommended replacement here: https://github.com/wpengine/atlas-content-modeler/blob/main/docs/end-of-life/index.md

= 0.25.0 - 2023-11-08 =

* **Fixed:** GraphQL queries now work properly when repeating Rich Text fields are optional and have no values.
* **Fixed:** GraphQL queries now work properly when Relationship fields are optional and have no connections.

= 0.24.0 - 2023-02-23 =

* **Fixed:** Querying posts with optional relationships will no longer raise an error in WPGraphQL when connections do not exist. Addresses changes made in WPGraphQL 1.13.x.

= 0.23.0 - 2022-11-15 =

* **Added:** Added a “Post Type Archives” option on the edit model screen to control custom post type archives (The WordPress `has_archive` setting. false by default).

= 0.22.0 - 2022-09-22 =

* **Added:** New `wp acm model change-id` WP-CLI command to change a model's ID and migrate existing posts to the new ID.
* **Changed:** Models can no longer be registered with IDs that match special WordPress Core names, such as ‘type’ and ‘theme’.
* **Changed:** Existing models using reserved model IDs are disabled to prevent fatal errors and unexpected behavior from WordPress Core.
* **Changed:** Disabled models with reserved model IDs display a message on the model index page, with a link to docs explaining how to change the model ID: https://github.com/wpengine/atlas-content-modeler/blob/main/docs/help/model-id-conflicts.md.

= 0.21.1 - 2022-09-07 =

* **Fixed:** No functional changes. Bumped version due to deploy issue.

= 0.21.0 - 2022-09-07 =

* **Added:** Added `validate_integer()` and `validate_decimal()` functions.
* **Fixed:** Decimal values ending in 0, such as 1.0, will now pass validation for validate_number_type() for integers.

= 0.20.0 - 2022-08-09 =

* **Added:** The email field gains a “make this field unique” setting so that emails can not be used more than once.
* **Added:** `wp acm reset` WP-CLI command to remove ACM models, taxonomies, posts, and media.

= 0.19.2 - 2022-07-25 =

* **Fixed:** Prevent a fatal error during the blueprint import cleanup process.

= 0.19.1 - 2022-07-13 =

* **Added:** Related entries in relationship fields now have an “Edit” link to view or edit them in a new tab.
* **Added:** Added model field API Identifier to model field list.
* **Fixed:** Fields will now save empty values when existing content is removed.
* **Fixed:** 0 can now be entered into number fields via the `insert_model_entry()` PHP function.
* **Fixed:** Decimals can now be entered into number fields via the `insert_model_entry()` PHP function.
* **Fixed:** You can now edit the case of model singular and plural names.

= 0.19.0 - 2022-06-29 =

* **Added:** The `wp acm blueprint import` WP-CLI command can now take a path to a local directory containing an `acm.json` blueprint manifest.
* **Added:** Developers working on the ACM plugin can now use the `wp acm blueprint import demo` WP-CLI command to import demo models with different field configurations.
* **Added:** Added field indexes to repeatable fields.
* **Fixed:** Number fields with '0' values are now saved correctly.

= 0.18.0 - 2022-06-16 =

* **Changed:** Text fields can now use “title” as their API identifier if “use this field as the entry title” is ticked.
* **Changed:** Field validation now returns translatable errors.
* **Changed:** Crud function insert_model_entry() will now append relationship ids.
* **Changed:** Text field entries via CRUD are now validated for min and max characters.
* **Changed:** Text field entries created via the PHP API are now validated for minRepeatable and maxRepeatable.
* **Changed:** Extended timeout for `wp acm blueprint import` command from 5 seconds to 15 seconds.
* **Changed:** Number field entries via CRUD are now validated for min, max, and step values as well as type.
* **Fixed:** Issue where adding a new repeating field to an existing model schema could break GraphQL queries under certain conditions.
* **Fixed:** Empty field values are no longer saved to the database.
* **Fixed:** Relationship fields no longer resolve as “null” in GraphQL results for models with different singular names and API identifiers.
* **Added:** Added email validation when using insert_model_entry() crud function.
* **Added:** Added ability to add newly created models from the top admin menu dropdown right after creation without a refresh of the page.
* **Added:** Added validation for repeatable text fields for insert_model_entry() crud function.
* **Added:** Added validation for repeatable date fields for insert_model_entry() crud function.
* **Added:** Added validation for repeatable number fields for insert_model_entry() crud function.
* **Added:** Added validation for repeatable email fields for insert_model_entry() crud function.
* **Added:** Added documentation for crud functions.

= 0.17.0 - 2022-05-05 =

* **Added:** Mutations support to create, update and delete entries via GraphQL. All fields are supported except media and relationships for now. Find examples at https://github.com/wpengine/atlas-content-modeler/blob/main/docs/mutations/index.md.
* **Added:** Function insert_entry_model() for inserting a model entry and fields.
* **Added:** Function update_model_entry() for updating an existing model entry and fields.
* **Added:** Function replace_relationship() to replace/add model entry relationships.
* **Added:** Function add_relationship() to append model entry relationships.
* **Added:** Function get_relationship() to retrieve the relationship object.
* **Added:** Function get_model() to retrieve the model schema.
* **Added:** Function get_field() to retrieve the model field schema.
* **Added:** Email field that includes the following: repeatable, entry title, required, and advanced settings to constrain emails to domain.
* **Added:** New "Add New Entry" option in content model dropdowns.
* **Added:** Added the ability to upload a ZIP file from your Local File System for BluePrints.
* **Changed:** Pressing enter in a repeating number, date or single line text field now moves focus to the next field, or adds a new field if focus is in the last field.
* **Changed:** Boolean fields in REST responses will now return `true` or `false` instead of `"on"` or `[empty string]`.
* **Changed:** Focus now moves to the new input field when adding a new repeating number, date or single line text field row.
* **Fixed:** Title values are now saved to the wp_posts table as expected, instead of being exposed with WP filters. This fixes a few things, such as queries that search the post title field.
* **Fixed:** Post slugs are now generated from the post title value, like they are for post types built into WordPress.
* **Fixed:** Taxonomy terms that already exist are skipped when importing a blueprint.

= 0.16.0 - 2022-04-06 =

* **Added:** The date field now has the “make this field repeatable” option to let publishers add multiple dates within each date field.
* **Changed:** The first field now gains focus when creating a new ACM post.
* **Changed:** Telemetry is no longer sent for staging sites (those with  *.wpengine.com domains). Telemetry for other domains still requires opt-in, and is off by default.

= 0.15.0 - 2022-03-24 =

* **Added:** Rich Text, Number and Media fields have a new “make repeatable” option. Enable it on new fields to let publishers add multiple rich text, number or image entries in one field.
* **Added:** Repeatable text fields now include a “minimum” and “maximum” repeatable limit in advanced settings.
* **Added:** Added a “Use Permalink Base” option on the edit model screen to set the WordPress `with_front` setting (true by default). Untick this to tell WordPress not to prefix your model entry URLs with custom prefixes from your WordPress permalink settings. For example, a site with a permalink structure of `/posts/%postname%/` will have post URLs of `/posts/your-acm-model-name/your-post/` by default. Edit your model and untick “Use Permalink Base” if you prefer a URL structure of `/your-acm-model-name/your-post/`.
* **Added:** `wp acm blueprint export` now accepts a `--wp-options` flag to export a comma-separated list of WordPress options from the `wp_options` table. No options are exported by default. Example: `wp acm blueprint export --wp-options='blogname, permalink_structure`
* **Added:** `wp acm blueprint import` now updates WordPress options if blueprints contain a `wp-options` key with a list of options values, keyed by option name.
* **Changed:** The `wp acm blueprint export` and `wp acm blueprint import` commands now include all taxonomies for collected post types, including category and post_tag taxonomy terms for the WordPress core 'post' type.
* **Fixed:** The delete model prompt no longer shows “undefined” in its title during model deletion.
* **Fixed:** Improved checkbox styling in field settings.
* **Fixed:** Options buttons now use the mouse pointer cursor.
* **Fixed:** The `wp acm blueprint import` command no longer reports “Could not read an acm.json file” if the blueprint zip file was renamed after creation.
* **Fixed:** Fixed issue where it was possible to improperly lead a model ID with a number.
* **Fixed:** Improved validation messages for integer and decimal values in the number field.
* **Fixed:** WordPress admin notices no longer overlay the Screen Options button on ACM entry pages.

= 0.14.0 - 2022-02-10 =

* **Added:** New `wp acm blueprint import` WP-CLI command to import ACM data programmatically. This prepares for future work to restore ACM blueprints. See `wp help acm blueprint import` for options.
* **Added:** New `wp acm blueprint export` WP-CLI command to export ACM data into a blueprint zip file. See `wp help acm blueprint export` for options.
* **Fixed:** Featured Image Fields - Previously, featured images allowed for videos and other media to display. Now the modal limits to images in line with how Core featured images work.
* **Fixed:** Fixed PHP notice that happens under certain conditions when a featured image is not provided.

= 0.13.0 - 2022-02-01 =

* **Added:** Text Repeater Field - Added repeatable property to single and multi line text fields for returning arrays of publisher defined strings.
* **Added:** Opt-in anonymous usage tracking to help us make Atlas Content Modeler better (disabled by default).
* **Changed:** The title field of a model can no longer be changed once set, unless you delete the original title field. This prepares upcoming work to save title field data to WordPress post titles, allowing title field content to be searchable.
* **Fixed:** Ensure Rich Text fields load for publishers even if WordPress Core editor scripts are slow to execute.

= 0.12.1 - 2022-01-12 =

* **Fixed:** Prevent PHP fatal errors on sites running < PHP 7.4 under certain conditions when no models exist.
* **Fixed:** Prevent model and taxonomy creation and updates if singular or plural labels conflict with existing WPGraphQL fields.

= 0.12.0 - 2021-12-15 =

* **Added:** Debug info is now added to GraphQL responses when GraphQL Debug Mode is enabled and a request for Private models is made as an unauthenticated user.
* **Fixed:** Improved display of admin notices on pages in the Content Modeler admin menu.
* **Fixed:** Saving a model that has no changes no longer causes an error to be displayed.

= 0.11.0 - 2021-12-01 =

* **Added:** Set any Media Field as the [Featured Image](https://www.wpgraphql.com/docs/media/#query-a-post-with-its-featured-image) for its model.
* **Added:** Create checkbox and radio button lists with the new Multiple Choice Field (beta).
* **Fixed:** Post titles are now available in WPGraphQL responses.
* **Fixed:** Prevent reserved taxonomy slugs from being used as taxonomy slug.
* **Fixed:** Used consistent labels to describe the taxonomy ID.
* **Fixed:** Changing model plural name now updates the sidebar menu item automatically.
* **Fixed:** Issue where sidebar menu doesn't expand under certain conditions.
* **Changed:** Standardized WP_Error codes for internal REST endpoints. All error statuses now use an acm_ prefix instead of a mix of wpe_ and atlas_content_modeler_.
* **Changed:** Removed dead code.

= 0.10.0 - 2021-11-18 =

* **Added:** Relationship fields with reverse references enabled are now editable from the reverse side. Tick “Configure Reverse Reference” when creating your relationship field to use reverse references.
* **Added:** Post slugs are now editable in ACM entries. Enable them via Screen Options when editing a post.
* **Fixed:** Integer number fields can no longer use decimal values for the step, minimum and maximum settings.

= 0.9.0 - 2021-11-03 =

* **Added:** Fields now have an optional description which can be used to inform publishers what a field is for. The description is displayed above the field on the post entry screen.
* **Added:** Relationship field now supports one-to-one and one-to-many connections.
* **Fixed:** ACM now prevents field slugs from conflicting with reserved slugs already used by WPGraphQL.
* **Fixed:** One-to-one field cardinality is now properly enforced.
* **Fixed:** API Identifier character limit is now properly enforced.
* **Fixed:** API Identifier validation now properly accounts for dashes and underscores.
* **Fixed:** Reverse API Identifier conflict detection no longer causes conflicts with the field being edited.
* **Fixed:** Added `publicly_queryable` parameter when registering custom post types. This fixes an issue caused by changes in [WPGraphQL #2134](https://github.com/wp-graphql/wp-graphql/issues/2134).
* **Changed:** Posts without a title field no longer have their title saved as "Auto Draft". The fallback title is now entry[post id goes here].

= 0.8.0 - 2021-10-13 =

* **Added:** Share models between sites using the new Export and Import models feature. Visit Content Modeler → Tools to get started.
* **Added:** Added "author" support to custom post types. This unlocks new functionality such as assigning specific users to a post, and querying posts by author in WPGraphQL.
* **Fixed:** Fixed bug where the model icon could not be changed when editing an existing model.
* **Fixed:** Fixed bug in the Number field where you could not define a Step value without also defining a Max value.
* **Fixed:** Improved duplicate field slug detection to ensure forward and reverse slugs for the Relationship field are unique in WPGraphQL.
* **Changed:** The `@wordpress/i18n` package is no longer bundled in the plugin's scripts, relying on the version that ships with WordPress instead.

= 0.7.0 - 2021-10-04 =

* **Added:** Relationship Field: one-to-one and one-to-many relationships were renamed to many-to-one and many-to-many to accurately reflect their function.
* **Added:** Relationship Field: fields can now optionally include reverse references.
* **Added:** Relationship Field: added [Beta] flag as the feature takes shape.
* **Added:** Chore: set "Requires at least" to WordPress version 5.7
* **Added:** Chore: set "Requires PHP" to version 7.2
* **Fixed:** Fixed bug where the app prompted about "Unsaved changes" when no changes had been made.

= 0.6.0 - 2021-09-09 =

* **Added:** Create one-to-one and one-to-many relationships between model entries with the new Relationship field.
* **Added:** A plugin icon will appear on the plugin update page during future updates.
* **Fixed:** Improved modal scroll behavior and positioning.

= 0.5.0 - 2021-08-12 =

* **Added:** You can now add custom Taxonomies and assign them to your models. Visit Atlas Content Modeler → Taxonomies to get started.
* **Added:** Models and Taxonomies submenu items now appear in the admin sidebar below Atlas Content Modeler.
* **Changed:** Refactored PHP tests.

= 0.4.2 - 2021-08-04 =

* **Added:** Ability to choose an icon when creating or editing a model.
* **Added:** Option to restrict file types for the media field.
* **Added:** Generate WordPress changelog from the Markdown changelog so that changes are visible from the WordPress changes modal.
* **Added:** Plugin developer improvements: GitHub Pull Request template; Code Climate configuration; Makefile for test environments.
* **Changed:** Change “API Identifier” field title on model entry forms to “Model ID” with a new description to better reflect its use.
* **Changed:** Continuous Integration: the generated plugin zip is now tested and verified before deploying.
* **Fixed:** Improve query generation for “Open in GraphiQL” to include lowercase model names and models with the same plural and singular name.
* **Fixed:** Improve sanitization of model slugs. Includes safe migration of existing model slugs.
* **Fixed:** Prevent a PHP warning during title filtering if post info can not be found.
* **Fixed:** Improve number field validation.

= 0.4.1 - 2021-06-24 =

* **Added:** Generate POT language file for translations.
* **Changed:** Use `include` in place of `require` so that missing or corrupt files do not take WordPress down.
* **Removed:** Removed the Multiple Choice field for now while we add support for custom choice API IDs.

= 0.4.0 - 2021-06-22 =

*  First public release. There may be breaking changes until 1.0.0.
* **Added:** New Multiple Choice field (beta) to create radio and checklist groups.
* **Added:** New “Send Feedback” button to share your experience with us.
* **Added:** New model option for “Private” or “Public” API visibility.
* **Added:** The Text field now includes an optional character minimum and maximum count in Advanced Settings.
* **Added:** The Number field now includes an optional minimum, maximum and step value in Advanced Settings.
* **Added:** Prompt to complete edits to an open field when attempting to open another field.
* **Added:** Added LICENSE, CONTRIBUTING and CHANGELOG files.
* **Changed:** REST responses now show Atlas Content Modeler fields under an `acm_fields` property.
* **Changed:** REST responses now display detailed information about media fields.
* **Changed:** Changed “Text Length” to “Input Type” in the Text field. Text length is now determined in Advanced Settings. Input type lets developers choose an input or textarea field.
* **Changed:** Increased the clickable area on dropdown menu items in the developer app.
* **Changed:** Adjusted styling in the publisher app.
* **Changed:** Refactored default value handling to improve field load times.
* **Changed:** Internal REST routes now include an `atlas` prefix.
* **Changed:** The README now includes a getting started guide.
* **Fixed:** Pressing return in the publisher app will now submit the form, instead of clearing fields.
* **Fixed:** Corrected an issue preventing fields from appearing in REST responses.
* **Fixed:** Prevented custom fields from showing in REST when `show_in_rest` is false.
* **Fixed:** Strings in the publisher and developer interfaces are now translatable.
* **Fixed:** Fixed Jest tests and add to Continuous Integration workflow.
* **Fixed:** Improved admin URL handling for WordPress sites hosted in a subfolder.
* **Fixed:** Improved plugin update error messages to include the name of the plugin reporting them.
* **Fixed:** Prevented a styling issue with the Content Modeler admin menu item.

= 0.3.0 - 2021-06-01 =

* **Added:** Developers can now mark fields as required.
* **Added:** Publishers will see inline errors prompting them to fill required fields.
* **Changed:** Rebranded to Atlas Content Modeler.
* **Changed:** Changed data storage format.
* **Changed:** Changed model data option name from wpe_content_model_post_types to atlas_content_modeler_post_types.
* **Changed:** Updated admin sidebar icons for settings and entries.
* **Changed:** Improved Rich Text fields, including adding media support.
* **Changed:** Open fields now close when another field is created or opened.
* **Changed:** Media fields now have a WPGraphQL type of MediaItem instead of String to enable complete queries for media information.
* **Fixed:** Improved model and field list appearance at mobile screen widths.
* **Fixed:** Improved client-side field validation for publishers.
* **Fixed:** Hid duplicate page title on new entry screens.
* **Fixed:** Corrected headers when editing entries.
* **Fixed:** Protected meta fields to prevent them appearing in the Custom Fields meta box.
* **Removed:** Repeater fields have been removed.
* **Removed:** Screen options have been removed from entry pages for content model post types.
* **Removed:** Post thumbnail support has been removed for content model post types.

= 0.2.0 - 2021-05-07 =

* **Added:** Publishers can now enter model entries via a form.
* **Added:** Developers have the option to set a field as the title field.
* **Added:** The plugin now checks for updates.
* **Added:** Model and field options now close on blur or when the escape key is pressed.
* **Added:** Model options include “Open in GraphiQL” if the WPGraphQL plugin is active.
* **Added:** Improved developer tooling for linting, including pre-commit hooks.
* **Changed:** The media field is restricted to single file uploads for now.
* **Removed:** Unused “Created on” column from the model table.

= 0.1.0 =

*  Initial version.
