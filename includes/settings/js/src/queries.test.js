import { getRootFields, getTitleFieldId } from "./queries";

describe("getRootFields", () => {
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

		expect(getRootFields(fields)).toStrictEqual(expected);
	});

	it("passes empty objects through unchanged", () => {
		expect(getRootFields({})).toStrictEqual({});
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
