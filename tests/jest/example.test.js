import React from 'react';
import renderer from 'react-test-renderer';
import App from '../../includes/settings/js/src/App';

describe('Example tests', () => {
	it('is true that true is true', () => {
		expect(true).toBe(true);
	});

	const app = renderer.create(
		<App/>
	);

	let tree = app.toJSON();

	it('React', () => {
		expect(tree).toMatchSnapshot();
	});
});

