import React, { useState } from "react";

export default function Field(props) {
	const { field, modelSlug } = props;
	return (
		<>
			<div id={`field-${field.slug}`} className={`field ${field.type}`}>
				{fieldMarkup(field, modelSlug)}
			</div>
		</>
	);
}

// @todo wire up to react-hook-form, validate data, display errors.
function fieldMarkup(field, modelSlug) {
	switch (field.type) {
		case "media":
			return (
				<>
					<label
						htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					<input type="text" className="hidden" name={`wpe-content-model[${modelSlug}][${field.slug}]`} id={`wpe-content-model[${modelSlug}][${field.slug}]`} defaultValue={field.value} />
					<input type="button" className="button button-primary button-large wpe-upload-btn" data-field={`wpe-content-model[${modelSlug}][${field.slug}]`} name={`upload_btn_${field.slug}`} id={`upload_btn_${field.slug}`} value="Upload Media" />
					{field.value && (
						<img src={field.value} alt={field.name} />
					)}
				</>
			);
		case "text":
		case "number": // @todo split this out to support mix/max/step/etc.
		case "date": // @todo split this out for proper browser and datepicker support
			return (
				<>
					<label
						htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					<br />
					<input
						type={`${field.type}`}
						name={`wpe-content-model[${modelSlug}][${field.slug}]`}
						id={`wpe-content-model[${modelSlug}][${field.slug}]`}
						defaultValue={field.value}
					/>
				</>
			);

		case "richtext":
			return (
				<>
					<label
						htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					<br />
					<textarea
						name={`wpe-content-model[${modelSlug}][${field.slug}]`}
						id={`wpe-content-model[${modelSlug}][${field.slug}]`}
						defaultValue={field.value}
					/>
				</>
			);

		case "boolean":
			const [checked, setChecked] = useState(
				field.value === "on" ? true : false
			);
			return (
				<>
					<input
						type="checkbox"
						name={`wpe-content-model[${modelSlug}][${field.slug}]`}
						id={`wpe-content-model[${modelSlug}][${field.slug}]`}
						checked={checked}
						onChange={(event) => setChecked(!checked)}
					/>
					<label
						htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
				</>
			);

		default:
			return `TODO: ${field.type} fields`;
	}
}
