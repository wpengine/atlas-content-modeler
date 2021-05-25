import React from "react";
import Icon from "../../../../components/icons";

export default function ClassicEditor({
	field,
	modelSlug,
	errors,
	validate,
	defaultError,
}) {
	return (
		<>
			<label htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}>
				{field.name}
			</label>
			<br />
			<textarea
				name={`wpe-content-model[${modelSlug}][${field.slug}]`}
				id={`wpe-content-model[${modelSlug}][${field.slug}]`}
				defaultValue={field.value}
				required={field.required}
			/>
			<span className="error">
				<Icon type="error" />
				<span role="alert">{defaultError}</span>
			</span>
		</>
	);
}
