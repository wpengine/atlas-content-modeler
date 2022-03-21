import React from "react";
import renderer from "react-test-renderer";
import RichText from "../RichText";
import * as uuid from "../uuid"; // Exposed so Jest can mock this function.

beforeEach(() => {
	jest.resetAllMocks();
});

describe("RichText", () => {
	it("renders a solo field if not repeatable", () => {
		const nonRepeatableProps = {
			modelSlug: "cats",
			field: {
				slug: "name",
				name: "Name",
				description: "The cat's real name.",
				value: "<p>Kahn The Destroyer</p>",
				isRepeatableRichText: false,
			},
		};
		// A dynamic ID would break snapshot testing, so we use a fixed one.
		jest.spyOn(uuid, "uuid").mockReturnValue("solo-field-id");

		const testSolo = renderer.create(<RichText {...nonRepeatableProps} />);
		expect(testSolo.toJSON()).toMatchSnapshot();
	});

	it("renders a repeating field if repeatable", () => {
		const repeatableProps = {
			modelSlug: "cats",
			field: {
				slug: "name",
				name: "Name",
				description: "The cat's real name.",
				value: ["<p>Kahn The Destroyer<p>"],
				isRepeatableRichText: true,
			},
		};

		// A dynamic ID would break snapshot testing, so we use a fixed one.
		jest.spyOn(uuid, "uuid").mockReturnValue("repeatable-field-id");

		const testRepeatable = renderer.create(
			<RichText {...repeatableProps} />
		);
		expect(testRepeatable.toJSON()).toMatchSnapshot();
	});
});
