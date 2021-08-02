import { toValidApiId } from ".";

describe("toValidApiId", () => {
	const cases = [
		[",,,#!!!strip_punctuation???$...", "strip_punctuation"],
		["⚠️⚠️⚠️strip_emoji🐇🐇🐇", "strip_emoji"],
		["strip accented éîøü", "stripAccented"],
		["1strip_leading_number", "strip_leading_number"],
		["123strip_leading_numbers", "strip_leading_numbers"],
		["     trim_white_space     ", "trim_white_space"],
		["Lower_case_initial_capitals", "lower_case_initial_capitals"],
		["_allows_leading_underscore", "_allows_leading_underscore"],
		["camel case    white    spaces", "camelCaseWhiteSpaces"],
		[
			"allow numbers after 1st character123",
			"allowNumbersAfter1stCharacter123",
		],
	];

	test.each(cases)("toValidApiId(%s) should be %s", (input, expected) => {
		expect(toValidApiId(input)).toEqual(expected);
	});
});
