module.exports = {
	name: "WPE Content Model",
	globals: {
		wp: {},
		lodash: {},
		atlasContentModeler: {},
	},
	moduleNameMapper: {
		"^.+\\.(css|less|scss)$": "jest-css-modules-transform",
	},
};
