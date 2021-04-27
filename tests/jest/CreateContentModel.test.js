import React from "react";
import renderer from "react-test-renderer";
import { BrowserRouter as Router } from "react-router-dom";
import CreateContentModel from "../../includes/settings/js/src/components/CreateContentModel";

describe("CreateContentModel tests", () => {
	const app = renderer.create(
		<Router>
			<CreateContentModel />
		</Router>
	);

	let tree = app.toJSON();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
