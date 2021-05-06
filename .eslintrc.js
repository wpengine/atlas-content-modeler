module.exports = {
	root: true,
	env: {
		browser: true,
		node: true,
	},
	settings: {
		react: {
			version: "detect",
		},
	},
	parserOptions: {
		parser: "babel-eslint",
		ecmaVersion: 2020,
		ecmaFeatures: {
			jsx: true,
			modules: true,
			experimentalObjectRestSpread: true,
			globalReturn: true,
		},
		sourceType: "module",
	},
	extends: ["plugin:react/recommended", "plugin:prettier/recommended"],
	plugins: [],
	// add your custom rules here
	ignorePatterns: ["**/dist"],
	rules: {
		"react/prop-types": 0,
		"react/no-unescaped-entities": 0,
		"react/no-children-prop": 0,
		"no-unsafe-optional-chaining": 0,
	},
};
