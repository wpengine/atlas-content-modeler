# Atlas Content Modeler Data Storage

Atlas Content Modeler stores schema and entry data in WordPress options and tables:

- Models and field schemas are stored in a WordPress option called `atlas_content_modeler_post_types`.
- ACM taxonomy schemas are stored in a WordPress option called `atlas_content_modeler_taxonomies`.
- Post entry data for all fields except for relationship fields is stored in the `postmeta` table.
- Post entry data for relationship fields is stored in a custom table called `acm_post_to_post` (for faster access and search for related posts).

## Important

These storage locations may change in future versions. We have an [ACM PHP API](https://github.com/wpengine/atlas-content-modeler/blob/main/docs/crud/index.md) to get and set data in a consistent way that will still work if the data location changes. This API will be more stable than attempting to access the above options and tables directly.
