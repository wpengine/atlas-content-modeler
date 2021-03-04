import React from 'react';
import renderer from 'react-test-renderer';
import CreateContentModel from "../../includes/settings/js/src/components/CreateContentModel";

describe('CreateContentModel tests', () => {
	const app = renderer.create(
		<CreateContentModel/>
	);

	let tree = app.toJSON();

	it('Renders a matching snapshot', () => {
		expect(tree).toMatchSnapshot();
	});
});

