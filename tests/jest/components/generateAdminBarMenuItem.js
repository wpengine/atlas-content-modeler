import { generateAdminBarMenuItem } from "../../includes/settings/js/src/utils";

describe("generateAdminBarMenuItem tests", () => {
	const mock = {
		slug: "cows",
		singular: "Cow",
	};

	it("Renders a matching snapshot", () => {
		const markup = generateAdminBarMenuItem(mock);
		expect(markup).toMatchSnapshot();
	});
});
