import React from "react";
import SoloEmail from "./SoloEmail";
import RepeatingEmail from "./RepeatingEmail";
import { __ } from "@wordpress/i18n";

export default function Email({
	field,
	errors,
	validate,
	modelSlug,
	defaultError,
}) {
	return field?.isRepeatableEmail ? (
		<RepeatingEmail
			field={field}
			modelSlug={modelSlug}
			validate={validate}
			errors={errors}
			defaultError={defaultError}
		/>
	) : (
		<SoloEmail
			field={field}
			modelSlug={modelSlug}
			errors={errors}
			validate={validate}
			defaultError={defaultError}
		/>
	);
}
