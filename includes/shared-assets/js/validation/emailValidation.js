export const getEscapedRegexValue = (value) => {
	return value.replace(/[.+?^${}()|[\]\\]/g, "\\$&").replace(/\*/g, ".*");
};

export const buildWildcardRegex = (values = "", delimiter = ",") => {
	const splitValues = values.split(delimiter).filter((x) => x);

	if (splitValues.length > 0) {
		const escapedValues = splitValues.map((value) => {
			return getEscapedRegexValue(value);
		});
		const valuesToString = escapedValues.join("|");
		return `.*\@(${valuesToString})$`;
	}

	return null;
};
