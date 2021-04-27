import React, { useState } from "react";

export default function Field(props) {
	const { field, modelSlug } = props;
	return (
		<>
			<div className={`field ${field.type}`}>
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
				<div>
					<label htmlFor="image_url">Image</label>
					<input type="text" name="image_url" id="image_url" className="regular-text" />
					<input type="button" name="upload-btn" id="upload-btn" className="button-secondary" value="Upload Image" />
				</div>
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
