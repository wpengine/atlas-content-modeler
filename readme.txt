=== Content Model Plugin ===
Contributors:
Tags:
Requires at least: 5.3
Tested up to: 5.7
Requires PHP: 7.0
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author: WP Engine

Headless WordPress content modeling plugin.

== Description ==

== Installation ==

== Frequently Asked Questions ==

= A question that someone might have =

== Screenshots ==

== Changelog ==

= Unreleased =
### Added
- Developers can now mark fields as required.
- Publishers will see inline errors prompting them to fill required fields.

### Fixed
- Hide duplicate page title on new entry screens.

= 0.2.0 - 2021-05-07 =
### Added
- Publishers can now enter model entries via a form.
- Developers have the option to set a field as the title field.
- The plugin now checks for updates.
- Model and field options now close on blur or when the escape key is pressed.
- Model options include “Open in GraphiQL” if the WPGraphQL plugin is active.
- Improved developer tooling for linting, including pre-commit hooks.

### Changed
- Repeater fields can no longer be created inside other repeater fields.
- The media field is restricted to single file uploads for now.

### Removed
- Unused “Created on” column from the model table.

= 0.1.0 =
- Initial version.
