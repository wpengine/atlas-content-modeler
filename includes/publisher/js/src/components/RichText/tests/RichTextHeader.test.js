import React from "react";
import renderer from "react-test-renderer";
import RichTextHeader from "../RichTextHeader";

describe("RichTextHeader", () => {
	const props = {
		modelSlug: "cats",
		field: {
			slug: "name",
			name: "Name",
			description: "The cat's real name.",
			value: "Kahn The Destroyer",
		},
	};
	const TestComponent = renderer.create(<RichTextHeader {...props} />);

	it("renders a matching snapshot", () => {
		expect(TestComponent.toJSON()).toMatchSnapshot();
	});
});
