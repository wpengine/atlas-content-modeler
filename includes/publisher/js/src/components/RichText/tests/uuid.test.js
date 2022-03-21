import { uuid } from "../uuid";

describe("uuid", () => {
	it("gives legal HTML IDs", () => {
		const legalHtmlIDPattern = /^[A-Za-z]+[\w\-\:\.]*$/; // See https://www.w3.org/TR/html4/types.html#type-id.
		expect(uuid()).toMatch(legalHtmlIDPattern);
	});

	it("gives different IDs each run", () => {
		const idCount = 100;
		const ids = [...Array(idCount)].map((v) => uuid());
		const unique = [...new Set(ids)];
		expect(unique).toHaveLength(idCount);
	});
});
