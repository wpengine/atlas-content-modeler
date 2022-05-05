import React from "react";
import { __ } from "@wordpress/i18n";

const EmailHeader = ({ modelSlug, field }) => {
	return (
		<>
			<label
				htmlFor={`atlas-content-modeler[${modelSlug}][${field.slug}]`}
			>
				{field.name}
			</label>
			{field?.required && (
				<p className="required">
					*{__("Required", "atlas-content-modeler")}
				</p>
			)}
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
