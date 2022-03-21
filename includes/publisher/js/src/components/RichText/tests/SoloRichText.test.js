import React from "react";
import renderer from "react-test-renderer";
import SoloRichText from "../SoloRichText";

describe("SoloRichText", () => {
	const props = {
		modelSlug: "cats",
		field: {
			slug: "name",
			name: "Name",
			description: "The cat's real name.",
			value: "Kahn The Destroyer",
		},
		fieldId: "field-123",
	};
	const TestComponent = renderer.create(<SoloRichText {...props} />);

	it("renders a matching snapshot", () => {
		expect(TestComponent.toJSON()).toMatchSnapshot();
	});
});
