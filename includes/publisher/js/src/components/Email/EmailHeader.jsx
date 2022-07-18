import React from "react";
import { __ } from "@wordpress/i18n";

const ValidationHelperLabel = ({ field }) => {
	if (field?.required && field?.isUnique) {
		return (
			<>
				<p className="required">
					*{__("Required, Unique", "atlas-content-modeler")}
				</p>
			</>
		);
	}

	if (field?.required) {
		return (
			<>
				<p className="required">
					*{__("Required", "atlas-content-modeler")}
				</p>
			</>
		);
	}
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
