import React from "react";
import { MediaSettings } from "../../../includes/settings/js/src/components/fields/AdvancedSettings";
import ShallowRenderer from "react-test-renderer/shallow";

describe("MediaSettings tests", () => {
	const allowed = "jpeg,jpg,pdf";
	const mock = {
		errors: {},
		getValues: jest.fn(() => {
			return allowed;
		}),
	};

	const renderer = new ShallowRenderer();
	renderer.render(<MediaSettings {...mock} />);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
