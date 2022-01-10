import React from "react";
import { act, create } from "react-test-renderer";
import Field from "../../../includes/publisher/js/src/components/Field";
import ShallowRenderer from "react-test-renderer/shallow";

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

const mediaField = {
	allowedTypes: "jpeg,jpg,pdf",
	id: "1624884901144",
	name: "Photo",
	slug: "photo",
	type: "media",
	value: "100",
	required: false,
};

const booleanField = {
	name: "Boolean",
	slug: "boolean",
	type: "boolean",
};

const dateField = {
	name: "Date",
	slug: "date",
	type: "date",
};

const richTextField = {
	name: "Rich Text",
	slug: "richtext",
	type: "richtext",
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

	it("renders a proper boolean field", () => {
		act(() => {
			root = create(
				<Field field={booleanField} modelSlug={model.slug} />
			);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});

	it("renders a proper date field", () => {
		act(() => {
			root = create(<Field field={dateField} modelSlug={model.slug} />);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});

	it("renders a rich text field", () => {
		const renderer = new ShallowRenderer();
		renderer.render(<Field field={richTextField} modelSlug={model.slug} />);
		const tree = renderer.getRenderOutput();
		expect(tree).toMatchSnapshot();
	});

	it("renders a media field", () => {
		const renderer = new ShallowRenderer();
		renderer.render(<Field field={mediaField} modelSlug={model.slug} />);
		const tree = renderer.getRenderOutput();
		expect(tree).toMatchSnapshot();
	});
});
