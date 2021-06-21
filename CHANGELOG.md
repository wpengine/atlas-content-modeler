# Atlas Content Modeler Changelog

## Unreleased
### Added
- Allow optional character minimum and maximum counts for Text fields.

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
