import { getRootFields, getChildrenOfField } from "./queries";

describe("getRootFields", () => {
	it("moves children to subfields", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456, parent: 123 },
			789: { id: 789, parent: 123 },
		};

		const expected = {
			123: {
				id: 123,
				subfields: {
					456: { id: 456, parent: 123, subfields: {} },
					789: { id: 789, parent: 123, subfields: {} },
				},
			},
		};

		expect(getRootFields(fields)).toStrictEqual(expected);
	});

	it("supports nested children", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456, parent: 123 },
			789: { id: 789, parent: 456 },
		};

		const expected = {
			123: {
				id: 123,
				subfields: {
					456: {
						id: 456,
						parent: 123,
						subfields: {
							789: { id: 789, parent: 456, subfields: {} },
						},
					},
				},
			},
		};

		expect(getRootFields(fields)).toStrictEqual(expected);
	});

	it("passes empty objects through unchanged", () => {
		expect(getRootFields({})).toStrictEqual({});
	});
});

describe("getChildrenOfField", () => {
	it("finds children and descendents of a parent field", () => {
		const fields = {
			456: { id: 456, parent: 123 },
			123: { id: 123 },
			789: { id: 789, parent: 456 },
		};

		const expected = [456, 789];

		expect(getChildrenOfField(123, fields)).toStrictEqual(expected);
	});

	it("gives empty array if no children found", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456 },
			789: { id: 789 },
		};

		const expected = [];

		expect(getChildrenOfField(123, fields)).toStrictEqual(expected);
	});
});
