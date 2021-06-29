import { generateSidebarMenuItem } from "../../includes/settings/js/src/utils";

describe("generateSidebarMenuItem tests", () => {
	const mock = {
		slug: "cows",
		plural: "Cows",
		modelIcon: "dashicons-saved",
	};

	it("Renders a matching snapshot", () => {
		const markup = generateSidebarMenuItem(mock);
		expect(markup).toMatchSnapshot();
	});
});
