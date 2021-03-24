import {getRootFields} from "./queries";

describe('getRootFields', () => {
	it('moves children to subfields', () => {
		const fields = {
			123: {id: 123},
			456: {id: 456, parent: 123},
			789: {id: 789, parent: 123},
		};

		const expected = {
			123: {
				id: 123, subfields: {
					456: {id: 456, parent: 123, subfields: {}},
					789: {id: 789, parent: 123, subfields: {}},
				}
			},
		};

		expect(getRootFields(fields)).toStrictEqual(expected);
	});

	it('supports nested children', () => {
		const fields = {
			123: {id: 123},
			456: {id: 456, parent: 123},
			789: {id: 789, parent: 456},
		};

		const expected = {
			123: {
				id: 123,
				subfields: {
					456: {
						id: 456,
						parent: 123,
						subfields: {
							789: {id: 789, parent: 456, subfields: {}},
						}
					},
				}
			},
		};

		expect(getRootFields(fields)).toStrictEqual(expected);
	});

	it('passes empty objects through unchanged', () => {
		expect(getRootFields({})).toStrictEqual({});
	});
});
