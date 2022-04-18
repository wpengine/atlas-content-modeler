import React, { useState } from "react";
import { __ } from "@wordpress/i18n";
import Icon from "../../../../components/icons";
import { buildWildcardRegex } from "../../../../shared-assets/js/validation/emailValidation";

export default function Email({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	const emailProps = {
		id: `atlas-content-modeler[${modelSlug}][${field.slug}]`,
		name: `atlas-content-modeler[${modelSlug}][${field.slug}]`,
		type: `${field.type}`,
		defaultValue: field.value,
		required: field.required,
		onChange: (event) => validate(event, field),
		pattern: buildWildcardRegex(field.allowedDomains),
	};

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

			<input {...emailProps} />
			<span className="error">
				<Icon type="error" />
				<span role="alert">{errors[field.slug] ?? defaultError}</span>
			</span>
		</>
	);
}
