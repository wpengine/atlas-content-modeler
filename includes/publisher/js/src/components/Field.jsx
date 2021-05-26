import React, { useState } from "react";
import MediaUploader from "./MediaUploader";
import ClassicEditor from "./ClassicEditor";
import Icon from "../../../../components/icons";

const defaultError = "This field is required";

export default function Field(props) {
	const { field, modelSlug } = props;
	const [errors, setErrors] = useState({});

	/**
	 * Adjusts the custom error feedback messages displayed below fields based
	 * on the HTML5 validation failure type.
	 *
	 * @param {object} event onChange
	 * @param {object} field
	 */
	function validate(event, field) {
		if (event.target.validity.valid) {
			return;
		}

		let error = defaultError;

		if (field.type === "text") {
			if (event.target.validity.tooShort) {
				error = "Text is too short";
			} else if (event.target.validity.tooLong) {
				error = "Text is too long";
			}
		}

		setErrors({ ...errors, [field.slug]: error });
	}

	return (
		<>
			<div
				id={`field-${field.slug}`}
				className={`field d-flex flex-column ${field.type}`}
			>
				{fieldMarkup(field, modelSlug, errors, validate)}
			</div>
		</>
	);
}

function fieldMarkup(field, modelSlug, errors, validate) {
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
						onChange={(event) => validate(event, field)}
						maxLength={field.textLength === "short" ? 50 : 500}
					/>
					<span className="error">
						<Icon type="error" />
						<span role="alert">
							{errors[field.slug] ?? defaultError}
						</span>
					</span>
				</>
			);
		case "number": // @todo split this out to support mix/max/step/etc.
		case "date": // @todo split this out for proper browser and datepicker support
			return (
				<>
					<label
						htmlFor={`wpe-content-model[${modelSlug}][${field.slug}]`}
					>
						{field.name}
					</label>
					<input
						type={`${field.type}`}
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

		case "richtext":
			return (
				<ClassicEditor
					field={field}
					modelSlug={modelSlug}
					errors={errors}
					validate={validate}
					defaultError={defaultError}
				/>
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
							<span role="alert">{defaultError}</span>
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
