import {
	sanitizeFields,
	getTitleFieldId,
	getOpenField,
	hasRelationships,
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

describe("hasRelationships", () => {
	it("returns true if a reference is found in another model", () => {
		const models = {
			model1: {
				fields: {
					123: { type: "relationship", reference: "no-match" },
				},
			},
			model2: {
				fields: { 123: { type: "relationship", reference: "bunnies" } },
			},
		};

		const modelToLookFor = "bunnies";

		expect(hasRelationships(models, modelToLookFor)).toEqual(true);
	});

	it("returns true if a reference is found in the same model", () => {
		const models = {
			bunnies: {
				fields: {
					123: { type: "relationship", reference: "bunnies" },
				},
			},
		};

		const modelToLookFor = "bunnies";

		expect(hasRelationships(models, modelToLookFor)).toEqual(true);
	});

	it("returns false if no reference is found", () => {
		const models = {
			model1: {
				fields: {
					123: { type: "relationship", reference: "no-match" },
				},
			},
			model2: {
				fields: {
					123: { type: "relationship", reference: "no-match2" },
				},
			},
		};

		const modelToLookFor = "bunnies";

		expect(hasRelationships(models, modelToLookFor)).toEqual(false);
	});
});
