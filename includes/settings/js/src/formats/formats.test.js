import { toValidApiId, toSanitizedKey, toGraphQLType } from ".";

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

describe("toSanitizedKey", () => {
	const cases = [
		[",,,#!!!strip_punctuation???$...", "strip_punctuation"],
		["⚠️⚠️⚠️strip_emoji🐇🐇🐇", "strip_emoji"],
		["strip accented éîøü", "stripaccented"],
		["strips    white    spaces", "stripswhitespaces"],
		["     trim_white_space     ", "trim_white_space"],
		["1allow_leading_number", "1allow_leading_numbe"],
		["123allow_leading_numbers", "123allow_leading_num"],
		["Lower_case_All_the_THINGS", "lower_case_all_the_t"],
		["_allows_leading_underscore", "_allows_leading_unde"],
		["-Allows-Hypens-", "-allows-hypens-"],
		["allow n after 1st c123", "allownafter1stc123"],
	];

	test.each(cases)("toSanitizedKey(%s) should be %s", (input, expected) => {
		expect(toSanitizedKey(input)).toEqual(expected);
	});

	test("limits character length to a custom value", () => {
		expect(toSanitizedKey("abcdefghi", 3)).toEqual("abc");
	});
});

describe("toGraphQLType", () => {
	const cases = [
		[",,,#!!!strip_punctuation???$...", "stripPunctuation", false],
		["⚠️⚠️⚠️strip_emoji🐇🐇🐇", "stripEmoji", false],
		["strip accented éîøü", "stripAccented", false],
		["1strip_leading_number", "stripLeadingNumber", false],
		["123strip_leading_numbers", "stripLeadingNumbers", false],
		["     trim_white_space     ", "trimWhiteSpace", false],
		["Lower_case_initial_capitals", "lowerCaseInitialCapitals", false],
		["_removes_leading_underscore", "removesLeadingUnderscore", false],
		["camel case    white    spaces", "camelCaseWhiteSpaces", false],
		["cap-first-word-with-dashes", "CapFirstWordWithDashes", true],
		["cap-first-word-with-spaces", "CapFirstWordWithSpaces", true],
		[
			"cap-first-word-with-underscores",
			"CapFirstWordWithUnderscores",
			true,
		],
	];

	test.each(cases)(
		"toGraphQLType(%s) should be %s",
		(input, expected, capFirst) => {
			expect(toGraphQLType(input, capFirst)).toEqual(expected);
		}
	);
});
