import React from 'react';
import { act, create } from 'react-test-renderer';
import Fields from '../../../includes/content-editing/js/src/components/Fields';

const model = {
	slug: 'geese',
	fields: {
		1: {
			id: 1,
			name: 'First Name',
			slug: 'firstName',
			type: 'text',
			value: 'Lucy',
			position: 0,
		},
		2: {
			id: 2,
			name: 'Last Name',
			slug: 'lastName',
			type: 'text',
			value: 'Goosey',
			position: 1,
		},
		3: {
			id: 3,
			name: 'Age',
			slug: 'age',
			type: 'number',
			value: '42',
			position: 2,
		},
	},
};

describe('Fields', () => {
	let root;
	it('when given a model, it renders a list of its fields', () => {
		act(() => {
			root = create(<Fields model={model} />);
		});

		expect(root.toJSON()).toMatchSnapshot();
	});
});
