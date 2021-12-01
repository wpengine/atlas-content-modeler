=== Atlas Content Modeler ===
Requires at least: 5.7
Tested up to: 5.8
Requires PHP: 7.2
Stable tag: 0.11.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author: WP Engine
Contributors: antpb, apmatthe, chriswiegman, jasonkonen, kevinwhoffman, markkelnar, matthewguywright, mindctrl, modernnerd, rfmeier, wpengine

A WordPress plugin to create custom post types, custom fields, and custom taxonomies for headless WordPress sites.

== Description ==
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