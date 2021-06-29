import React from "react";
import MediaUploader from "../../../includes/publisher/js/src/components/MediaUploader";
import ShallowRenderer from "react-test-renderer/shallow";

describe("MediaUploader tests", () => {
	const mock = {
		modelSlug: "goose",
		field: {
			allowedTypes: "jpeg,jpg,pdf",
			id: "1624884901144",
			name: "Photo",
			slug: "photo",
			type: "media",
			value: "100",
			required: false,
		},
		required: false,
	};
	const renderer = new ShallowRenderer();
	renderer.render(<MediaUploader {...mock} />);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
