import React, { useState } from "react";
import MediaUploader from "./MediaUploader";
import Icon from "../../../../components/icons";

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
	modelSlug = modelSlug.toLowerCase();
	switch (field.type) {
		case "media":
			return (
				<MediaUploader
					field={field}
					modelSlug={modelSlug}
					required={field.required}
				/>
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
						required={field.required}
					/>
					<span className="error">
						<Icon type="error" />
						<span role="alert">This field is required</span>
					</span>
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
						required={field.required}
					/>
					<span className="error">
						<Icon type="error" />
						<span role="alert">This field is required</span>
					</span>
				</>
			);

		case "boolean":
			const [checked, setChecked] = useState(field.value === "on");
			return (
				<>
					<label
						className="check-container"
						htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
					>
						{field.name}
						<input
							type="checkbox"
							name={`wpe-content-model[${modelSlug}][${field.slug}]`}
							id={`wpe-content-model[${modelSlug}][${field.slug}]`}
							checked={checked}
							onChange={(event) => setChecked(!checked)}
							required={field.required}
						/>
						<span className="error">
							<Icon type="error" />
							<span role="alert">This field is required</span>
						</span>
						{/* span is used for custom checkbox styling purposes */}
						<span className="checkmark"></span>
					</label>
				</>
			);

		default:
			return `TODO: ${field.type} fields`;
	}
}
