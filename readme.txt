=== Atlas Content Modeler ===
Requires at least: 5.7
Tested up to: 5.8
Requires PHP: 7.2
Stable tag: 0.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author: WP Engine
Contributors: antpb, apmatthe, chriswiegman, kevinwhoffman, markkelnar, matthewguywright, mindctrl, modernnerd, rfmeier, wpengine

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

== Screenshots ==
1. (work in progress) Description here... screenshot-1.png in assets/wporg
2. screenshot-2.png in assets/wporg
3. screenshot-2.png in assets/wporg

== Frequently Asked Questions ==
= Can Atlas Content Modeler be used with traditional WordPress sites? =
ACM is primarily intended for headless WordPress applications. For that reason, the WordPress REST API and WPGraphQL are the only two officially supported APIs. That said, it is possible to fetch the data for your models in a traditional WordPress site by using the `rest_do_request()` PHP function that the REST API provides or the `graphql()` PHP function that WPGraphQL provides.
