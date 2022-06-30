import React from "react";
import renderer from "react-test-renderer";
import App from "../../includes/settings/js/src/App";

describe("App", () => {
	window.atlasContentModeler = {
		acm_plugin_data: {
			Version: "",
		},
		stats: {
			modelsCounts: [
				{ title: "test", count: 5 },
				{ title: "test2", count: 100 },
			],
			taxonomies: [],
			relationships: {
				totalRelationshipConnections: 10,
				mostConnectedEntries: [],
			},
			recentModelEntries: [],
		},
		initialState: {},
	};

	const app = renderer.create(<App />);

	let tree = app.toJSON();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
