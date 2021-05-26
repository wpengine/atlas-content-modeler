# Bootstrap

**Required Software**
- [Bootstrap]()
- [Nodejs]()

## Namespaced Bootstrap Files

Import the following into your scss file for each project where you want Bootstrap css. You may need to update the file path for the imports.

```
.atlas-content-modeler {
	@import "../../../node_modules/bootstrap/scss/_functions";
	@import "../../../node_modules/bootstrap/scss/_variables";
	@import "../../../node_modules/bootstrap/scss/_mixins";
	@import "../../../node_modules/bootstrap/scss/vendor/_rfs";
	@import "../../../node_modules/bootstrap/scss/_containers";
	@import "../../../node_modules/bootstrap/scss/_grid";
	@import "../../../node_modules/bootstrap/scss/_helpers";
	@import "../../../node_modules/bootstrap/scss/_utilities";
	@import "../../../node_modules/bootstrap/scss/utilities/_api";
}
```

Currently, we are only importing what is needed for the grid and utilities from Bootstrap to keep file size down.

Simply change ```.atlas-content-modeler``` to whatever the namespace should be. Wrapping the list of imports from bootstrap with a class is what accomplishes this. When the build is done on this file, you will see everything in bootstrap namespaced accordingly.
