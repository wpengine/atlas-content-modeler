import {
	sanitizeFields,
	getTitleFieldId,
	getOpenField,
	getRelationships,
} from "./queries";

describe("sanitizeFields", () => {
	it("moves children to subfields", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456 },
			789: { id: 789 },
		};

		const expected = {
			123: { id: 123 },
			456: { id: 456 },
			789: { id: 789 },
		};

		expect(sanitizeFields(fields)).toStrictEqual(expected);
	});

	it("passes empty objects through unchanged", () => {
		expect(sanitizeFields({})).toStrictEqual({});
	});
});

describe("getTitleFieldId", () => {
	it("gets the ID of the field set as the entry title", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456 },
			789: { id: 789, isTitle: true },
		};

		const expected = 789;

		expect(getTitleFieldId(fields)).toEqual(expected);
	});
	it("gives an empty string if no field is set as the entry title", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456 },
			789: { id: 789 },
		};

		const expected = "";

		expect(getTitleFieldId(fields)).toEqual(expected);
	});
});

describe("getOpenField", () => {
	it("returns the open field if there is one", () => {
		const fields = {
			123: { id: 123, open: true },
			456: { id: 456 },
			789: { id: 789 },
		};

		const expected = { id: 123, open: true };

		expect(getOpenField(fields)).toEqual(expected);
	});
	it("returns an empty object if no field is open", () => {
		const fields = {
			123: { id: 123 },
			456: { id: 456 },
			789: { id: 789 },
		};

		const expected = {};

		expect(getOpenField(fields)).toEqual(expected);
	});
});

describe("getRelationships", () => {
	it("returns field IDs if a reference is found in another model", () => {
		const models = {
			model1: {
				slug: "model1",
				fields: {
					123: {
						id: 123,
						type: "relationship",
						reference: "no-match",
					},
				},
			},
			model2: {
				slug: "model2",
				fields: {
					456: {
						id: 456,
						type: "relationship",
						reference: "bunnies",
					},
				},
			},
		};

		const modelToLookFor = "bunnies";

		const expected = [{ model: "model2", id: 456 }];

		expect(getRelationships(models, modelToLookFor)).toEqual(expected);
	});

	it("returns field IDs for relationship fields found across multiple models", () => {
		const models = {
			model1: {
				slug: "model1",
				fields: {
					123: {
						id: 123,
						type: "relationship",
						reference: "bunnies",
					},
					456: {
						id: 456,
						type: "relationship",
						reference: "bunnies",
					},
				},
			},
			model2: {
				slug: "model2",
				fields: {
					123: {
						id: 123,
						type: "relationship",
						reference: "bunnies",
					},
					456: {
						id: 456,
						type: "relationship",
						reference: "no-bunnies",
					},
				},
			},
		};

		const modelToLookFor = "bunnies";

		const expected = [
			{ model: "model1", id: 123 },
			{ model: "model1", id: 456 },
			{ model: "model2", id: 123 },
		];

		expect(getRelationships(models, modelToLookFor)).toEqual(expected);
	});

	it("returns empty array if no reference is found", () => {
		const models = {
			model1: {
				slug: "model1",
				fields: {
					123: {
						id: 123,
						type: "relationship",
						reference: "no-match",
					},
				},
			},
			model2: {
				slug: "model2",
				fields: {
					123: {
						id: 123,
						type: "relationship",
						reference: "no-match2",
					},
				},
			},
		};

		const modelToLookFor = "bunnies";

		const expected = [];

		expect(getRelationships(models, modelToLookFor)).toEqual(expected);
	});

	it("returns empty array if models is empty", () => {
		expect(getRelationships({}, "bunnies")).toEqual([]);
	});
});
