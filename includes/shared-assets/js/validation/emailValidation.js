export const buildWildcardRegex = (value) => {
	let escapedRegexString = value.replace(/[.+?^${}()|[\]\\]/g, "\\$&");
	escapedRegexString = escapedRegexString.replace(/\*/g, ".*");
	return new RegExp(`.*\@${escapedRegexString}$`);
};

export const isEmailDomainValid = (email, domains) => {
	if (!email || !domains) {
		throw new Error(
			"Unable to validate email by domain. Email or domain is missing."
		);
	}

	const splitDomains = domains.split(",").filter((x) => x);
	const isEmailValid = splitDomains.filter((domain) => {
		const isDomainValid = buildWildcardRegex(domain).test(email);
		return isDomainValid;
	});

	return isEmailValid.length > 0;
};
