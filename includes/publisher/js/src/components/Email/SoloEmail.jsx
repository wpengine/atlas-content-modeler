import React from "react";
import { __ } from "@wordpress/i18n";
import Icon from "../../../../../components/icons";
import { buildWildcardRegex } from "../../../../../shared-assets/js/validation/emailValidation";
import EmailHeader from "./EmailHeader";

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
			<EmailHeader modelSlug={modelSlug} field={field} />
			<input {...emailProps} />
			<span className="error">
				<Icon type="error" />
				<span role="alert">{errors[field.slug] ?? defaultError}</span>
			</span>
		</>
	);
}
