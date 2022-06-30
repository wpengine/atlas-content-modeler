import React from "react";
import renderer from "react-test-renderer";
import App from "../../includes/settings/js/src/App";

describe("App", () => {
	const app = renderer.create(<App />);
	const atlasContentModeler = {
		stats: {
			modelsCounts: [
				{ title: "test", count: 5 },
				{ title: "test2", count: 100 },
			],
		},
	};

	let tree = app.toJSON();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
