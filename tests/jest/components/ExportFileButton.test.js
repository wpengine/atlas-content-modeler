import ExportFileButton from "../../../includes/settings/js/src/components/ExportFileButton";
import ShallowRenderer from "react-test-renderer/shallow";
import React from "react";

describe("ExportFileButton tests", () => {
	const fileData = {};
	const renderer = new ShallowRenderer();
	renderer.render(<ExportFileButton fileData={fileData} />);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
