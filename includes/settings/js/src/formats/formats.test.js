import { toValidApiId, toPostTypeSlug } from ".";

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

describe("toPostTypeSlug", () => {
	const cases = [
		[",,,#!!!strip_punctuation???$...", "strip_punctuation"],
		["⚠️⚠️⚠️strip_emoji🐇🐇🐇", "strip_emoji"],
		["strip accented éîøü", "stripaccented"],
		["strips    white    spaces", "stripswhitespaces"],
		["     trim_white_space     ", "trim_white_space"],
		["1allow_leading_number", "1allow_leading_number"],
		["123allow_leading_numbers", "123allow_leading_numbers"],
		["Lower_case_All_the_THINGS", "lower_case_all_the_things"],
		["_allows_leading_underscore", "_allows_leading_underscore"],
		["-Allows-Hypens-", "-allows-hypens-"],
		[
			"allow numbers after 1st character123",
			"allownumbersafter1stcharacter123",
		],
	];

	test.each(cases)("toPostTypeSlug(%s) should be %s", (input, expected) => {
		expect(toPostTypeSlug(input)).toEqual(expected);
	});
});
