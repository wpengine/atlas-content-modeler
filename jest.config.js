module.exports = {
	name: "WPE Content Model",
	globals: {
		wp: {},
		lodash: {},
		atlasContentModeler: {},
	},
	moduleNameMapper: {
		"acm-analytics": "<rootDir>/includes/shared-assets/js/analytics.js",
		"acm-icons": "<rootDir>/includes/components/icons/index.js",
		"^.+\\.(css|less|scss)$": "jest-css-modules-transform",
	},
};
