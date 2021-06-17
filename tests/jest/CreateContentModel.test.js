import React from "react";
import { BrowserRouter as Router } from "react-router-dom";
import CreateContentModel from "../../includes/settings/js/src/components/CreateContentModel";
import ShallowRenderer from "react-test-renderer/shallow";

describe("CreateContentModel tests", () => {
	const renderer = new ShallowRenderer();
	renderer.render(
		<Router>
			<CreateContentModel />
		</Router>
	);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
