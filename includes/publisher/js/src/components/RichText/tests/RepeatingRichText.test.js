import React from "react";
import renderer from "react-test-renderer";
import RepeatingRichText from "../RepeatingRichText";

describe("RepeatingRichText", () => {
	const props = {
		modelSlug: "cats",
		field: {
			slug: "name",
			name: "Name",
			description: "The cat's real name.",
			value: "Kahn The Destroyer",
		},
		values: [{ id: "123", value: "<p>Kahn The Destroyer</p>" }],
		setValues: () => [],
		uuid: () => "field-123456789",
	};
	const TestComponent = renderer.create(<RepeatingRichText {...props} />);

	it("renders a matching snapshot", () => {
		expect(TestComponent.toJSON()).toMatchSnapshot();
	});
});
