import React from "react";
import { __ } from "@wordpress/i18n";

const ValidationHelperLabel = ({ field }) => {
	let validationHelperMessages = [];

	if (field?.required) {
		validationHelperMessages.push("Required");
	}

	if (field?.isUnique) {
		validationHelperMessages.push("Unique");
	}

	if (validationHelperMessages.length > 0) {
		const message = validationHelperMessages.join(", ");
		return (
			<>
				<p className="required">
					*{__(message, "atlas-content-modeler")}
				</p>
			</>
		);
	}

	return <></>;
};

const EmailHeader = ({ modelSlug, field }) => {
	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			<ValidationHelperLabel field={field} />
			{field?.description && (
				<p className="help mb-0">{field.description}</p>
			)}
			{field?.allowedDomains && (
				<p className="help mb-0">
					Allowed domains :{" "}
					{field.allowedDomains.replaceAll(",", ", ")}
				</p>
			)}
		</>
	);
};

export default EmailHeader;
