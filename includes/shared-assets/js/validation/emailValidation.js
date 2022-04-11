const buildWildcardRegex = (value) => {
	return new RegExp(`.*\/${value}$`);
};

const validateByConstraint = (value, constraint) => {
	if (value && constraint) {
		// console.log("value : ", value);
		// console.log("constraint : ", constraint);
		// Build regex by constraint
		// wildcard
		// domain?
		// /.*(gmail.com|wpengine.com|.*\.edu|.*\.?flywheel.com)$/gm
		if (constraint.includes("*")) {
			const constraintWithoutAsterisk = constraint.replace("*", "");
			const wildCardRegexTest = buildWildcardRegex(
				constraintWithoutAsterisk
			);
			const isValid = wildCardRegexTest.test(value);
			// console.log("Email Domain with Wildcard IsValid : ", isValid);
			return isValid;
		} else {
			const domainRegex = new RegExp(`.*${constraint}$`);
			const isValid = domainRegex.test(value);
			// console.log("Email Domain IsValid : ", isValid);
			return isValid;
		}
	}

	return false;
};

export const isValidDomain = (email, domains) => {
	if (!email || !domains) {
		throw new Error(
			"Unable to validate email by domain. Email or domain is missing."
		);
	}

	const splitDomains = domains.split(",");
	const isEmailValid = splitDomains.forEach((domain) => {
		return validateByConstraint(email, domain);
	});

	// console.log("email : ", email);
	// console.log("splitDomains : ", splitDomains);
	// console.log("isEmailValid : ", isEmailValid);

	return isEmailValid;
};
