import ImportFileButton from "../../../includes/settings/js/src/components/ImportFileButton";
import ShallowRenderer from "react-test-renderer/shallow";
import React from "react";

describe("ImportFileButton tests", () => {
	const fileData = {};
	const renderer = new ShallowRenderer();
	renderer.render(<ImportFileButton fileData={fileData} />);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
