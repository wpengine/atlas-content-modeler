import React from "react";
import ShallowRenderer from "react-test-renderer/shallow";
import {
	Button,
	TertiaryButton,
	FieldButton,
	LinkButton,
	WarningButton,
	DarkButton,
} from "./Buttons";

describe("Button", () => {
	const renderer = new ShallowRenderer();
	renderer.render(<Button>Button content</Button>);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});

describe("TertiaryButton", () => {
	const renderer = new ShallowRenderer();
	renderer.render(
		<TertiaryButton>TertiaryButton Button content</TertiaryButton>
	);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});

describe("FieldButton", () => {
	describe("inactive FieldButton", () => {
		const renderer = new ShallowRenderer();
		renderer.render(<FieldButton active>Field Button content</FieldButton>);
		it("Renders a matching snapshot", () => {
			expect(renderer.getRenderOutput()).toMatchSnapshot();
		});
	});

	describe("active FieldButton", () => {
		const renderer = new ShallowRenderer();
		renderer.render(<FieldButton>Field Button content</FieldButton>);
		it("Renders a matching snapshot", () => {
			expect(renderer.getRenderOutput()).toMatchSnapshot();
		});
	});
});

describe("LinkButton", () => {
	const renderer = new ShallowRenderer();
	renderer.render(<LinkButton>Link Button content</LinkButton>);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});

describe("WarningButton", () => {
	const renderer = new ShallowRenderer();
	renderer.render(<WarningButton>Warning Button content</WarningButton>);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});

describe("DarkButton", () => {
	const renderer = new ShallowRenderer();
	renderer.render(<DarkButton>Dark Button content</DarkButton>);

	const tree = renderer.getRenderOutput();

	it("Renders a matching snapshot", () => {
		expect(tree).toMatchSnapshot();
	});
});
