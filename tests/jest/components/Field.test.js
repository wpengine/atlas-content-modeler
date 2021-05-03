import React from "react";
import { act, create } from "react-test-renderer";
import Field from "../../../includes/content-editing/js/src/components/Field";

const model = {
	slug: "geese",
};

const textField = {
	name: "First Name",
	slug: "firstName",
	type: "text",
	value: "John",
};

const numberField = {
	name: "Age",
	slug: "age",
	type: "number",
	value: "100",
};

describe("Field", () => {
	let root;

	it("renders a proper text field", () => {
		act(() => {
			root = create(<Field field={textField} modelSlug={model.slug} />);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});

	it("renders a proper number field", () => {
		act(() => {
			root = create(<Field field={numberField} modelSlug={model.slug} />);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});
});
