import React from "react";
import renderer from "react-test-renderer";
import App from "../../includes/settings/js/src/App";

describe("App", () => {
	const app = renderer.create(<App />);

	let tree = app.toJSON();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
