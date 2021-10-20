import React from "react";
import { act, create } from "react-test-renderer";
import ActionButtons from "./ActionButtons";

describe("ActionButtons", () => {
	let root;

	it("renders a publish button", () => {
		act(() => {
			root = create(<ActionButtons isEditMode={false} />);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});

	it("renders an update button", () => {
		act(() => {
			root = create(<ActionButtons isEditMode={true} />);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});
});
